<?php 
session_start();
ini_set('display_errors', true);
error_reporting(-1);

// Include the Composer autoloader
include './vendor/autoload.php';

// Include the storage models
include './model_client.php';
include 'model_scope.php';
include 'model_session.php';

// New Slim app
$app = new \Slim\Slim();
// use \League\OAuth2\Server\Util\Request;
// Initiate the Request handler
$request = new \League\OAuth2\Server\Util\Request();

// Initiate the auth server with the models
$server = new \League\OAuth2\Server\Authorization(new ClientModel, new SessionModel, new ScopeModel);

// Enable support for the authorization code grant
$server->addGrantType(new \League\OAuth2\Server\Grant\AuthCode());




// Clients will redirect to this address
$app->get('/', function () use ($server, $app) {
	try {
		
	
	// Tell the auth server to check the required parameters are in the query string
		$params = $server->getGrantType('authorization_code')->checkAuthoriseParams();
		// Session::put('client_id', $params['client_id']); // client_id=I6Lh72kTItE6y29Ig607N74M7i21oyTo
	    // Session::put('client_details', $params['client_details']); // client_details=User details
	    // Session::put('redirect_uri', $params['redirect_uri']); // redirect_uri=http://client.dev/signin/redirect
	    // Session::put('response_type', $params['response_type']); // response_type=code
	    // Session::put('scopes', $params['scopes']); //scopes=user
	    
	    // $_SESSION['client_id'] = $params['client_id']; // client_id=I6Lh72kTItE6y29Ig607N74M7i21oyTo
	    // $_SESSION['client_details'] = $params['client_details']; // client_details=User details
	    // $_SESSION['redirect_uri'] = $params['redirect_uri']; // redirect_uri=http://client.dev/signin/redirect
	    // $_SESSION['response_type'] = $params['response_type']; // response_type=code
	    // $_SESSION['scopes'] = $params['scopes']; //scopes=user
	    
	    $_SESSION['params'] = serialize($params);
	    
	    // url decoded
	    // ?client_id=I6Lh72kTItE6y29Ig607N74M7i21oyTo&client_details=User details&redirect_uri=http://client.dev/signin/redirect&response_type=code&scopes=user
	    // url encoded
	    // ?client_id=I6Lh72kTItE6y29Ig607N74M7i21oyTo&client_details=User details&redirect_uri=http%3A%2F%2Fclient.dev%2Fsignin%2Fredirect&response_type=code&scopes=user
			
	// 	
		
		
		// Save the verified parameters to the user's session
		// $_SESSION['params'] = serialize($params);
	
		// Redirect the user to sign-in
		//$app->redirect('/oauth.php/signin');
		// var_dump($_SESSIONS);
		return $app->Redirect('/oauth2-example-auth-server/oauth.php/signin');
	} catch ( Oauth2\Exception\ClientException $e) {
		echo $e;
        // Throw an error here which says what the problem is with the
        //  auth params

    } catch (Exception $e) {
		echo $e;
	}	 

});




// Sign-in
$app->get('/signin', function () {

	// Check the authorization params are set
	if ( ! isset($_SESSION['params']))
	{
		var_dump($_SESSION);
		// throw new Exception('Missing auth parameters');
		return;
	}

	// Get the params from the session
	$params = unserialize($_SESSION['params']);
	?>

	<form method="post">
		<h1>Sign in to <?php echo $params['client_details']['name']; ?></h1>

		<p>
			<label for="username">Username: </label>
			<input type="text" name="username" id="password" value="alex">
		</p>
		<p>
			<label for="password">Password: </label>
			<input type="password" name="password" id="password" value="password">
		</p>
		<p>
			<input type="submit" name="submit" id="submit" value="Sign in">
		</p>
	</form>

	<?php

});




// Process sign-in form submission
$app->post('/signin', function () use ($app) {

	// Check the auth params are in the session
	if ( ! isset($_SESSION['params']))
	{
		throw new Exception('Missing auth parameters');
	}

	$params = unserialize($_SESSION['params']);

	// Check the user's credentials
	if ($_POST['username'] === 'alex' && $_POST['password'] === 'password')
	{
		// Add the user ID to the auth params and forward the user to authorise the client
		$params['user_id'] = 1;
		// $params['user_id'] = $_SESSION['user_id'];
		$_SESSION['params'] = serialize($params);
		$app->redirect('/oauth2-example-auth-server/oauth.php/authorise');
	}

	// Wrong username/password
	else
	{
		$app->redirect('/oauth2-example-auth-server/oauth.php/signin');
	}

});



// The user authorises the app
$app->get('/authorise', function () use ($app) {

	// Check the auth params are in the session
	if ( ! isset($_SESSION['params']))
	{
		throw new Exception('Missing auth parameters');
	}

	$params = unserialize($_SESSION['params']);

	// Check the user is signed in
	if ( ! isset($params['user_id']))
	{		
		$app->redirect('/oauth2-example-auth-server/oauth.php/signin');
	}	
	?>

	<h1>Authorise <?php echo $params['client_details']['name']; ?></h1>

	<p>
		The application <strong><?php echo $params['client_details']['name']; ?></strong> would like permission to access your:
	</p>

	<ul>
		<?php foreach ($params['scopes'] as $scope): ?>
			<li>
				<?php echo $scope['name']; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<p>
		<form method="post" style="display:inline">
			<input type="submit" name="approve" id="approve" value="Approve">
		</form>

		<form method="post" style="display:inline">
			<input type="submit" name="deny" id="deny" value="Deny">
		</form>
	</p>

	<?php
});



// Process authorise form
$app->post('/authorise', function() use ($server, $app) {

	// Check the auth params are in the session
	if ( ! isset($_SESSION['params']))
	{
		throw new Exception('Missing auth parameters');
	}

	$params = unserialize($_SESSION['params']);

	// Check the user is signed in
	if ( ! isset($params['user_id']))
	{
		echo "asdfoasdfasdf";
		return;
		$app->redirect('/oauth2-example-auth-server/oauth.php/signin');
	}

	// Check if the client should be automatically approved
    //$autoApprove = ($params['client_details']['auto_approve'] === '1') ? true : false;
	$autoApprove = false;
	
	// If the user approves the client then generate an authoriztion code
	if ( isset($_POST['approve']) || $autoApprove === true)
	{
		//var_dump($params);
		
		$code = $server->getGrantType('authorization_code')->newAuthoriseRequest('user', $params['user_id'], $params);

		echo '<p>The user authorised a request and so would be redirected back to the client...</p>';

		// Generate the redirect URI
		return $app->redirect(\League\OAuth2\Server\Util\RedirectUri::make($params['redirect_uri'], array(
			'code' => $code,
			'state'	=> $params['state']
		)));
		
	}

	// The user denied the request so send them back to the client with an error
	elseif (isset($_POST['deny']))
	{
		echo '<p>The user denied the request and so would be redirected back to the client...</p>';
		return $app->redirect(\League\OAuth2\Server\Util\RedirectUri::make($params['redirect_uri'], array(
			'error' => 'access_denied',
			'error_message' => $server::getExceptionMessage('access_denied'),
			'state'	=> $params['state']
		)));
	}

});



// The client will exchange the authorization code for an access token
$app->post('/access_token', function() use ($server) {

	header('Content-type: application/javascript');

	try {

		// Issue an access token
		$p = $server->issueAccessToken();
		echo json_encode($p);

	}

	catch (Exception $e)
	{
		// Show an error message
		echo json_encode(array('error' => $e->getMessage(), 'error_code' => $e->getCode()));
	}

});

$app->run();

<?php
require_once __DIR__ .'/vendor/autoload.php';

//clear any access tokens from previous sessions
if(isset($_SESSION['access_token'])){
	unset($_SESSION['access_token']);
}

//get the client 
$client = new Google_Client();
$client->setAuthConfigFile('client_secret.json');
$client->addScope('http://www.google.com/m8/feeds/');
$client->setRedirectUri('http://localhost/google_people/redirect.php'); //where the user gets sent to from the authorization url

//set the authorization url and go there
$auth_url = $client->createAuthUrl();
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
?>
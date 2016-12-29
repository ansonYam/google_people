<?php
require_once __DIR__ .'/vendor/autoload.php';

//quit if we didn't come here with an authorization url
if (!isset($_GET['code'])) {
    die('No auth url present.');
}

//get the client 
$client = new Google_Client();
$client->setAuthConfigFile('client_secret.json');
$client->addScope('http://www.google.com/m8/feeds/');

//exchange auth url for an access token 
$client->authenticate($_GET['code']);

//if the access token exists, then retrieve it, then head over to 'test.php'
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
} else { 
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/google_people/test.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
?>
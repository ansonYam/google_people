<?php 
require_once __DIR__ .'/vendor/autoload.php';
use rajeshtomjoe\googlecontacts\factories\ContactFactory;

session_start();

//configure a new client specifying the scope of what you want to access (contacts, google drive...)
$client = new Google_Client();
$client->setAuthConfig('client_secret.json'); //downloaded client secret, keep it away from server
$client->addScope('https://www.google.com/m8/feeds/');

//request authorization from user 
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  
$groupId = ContactFactory::createGroup("Imported " . date("Y-m-d"));

//open a connection to the local sql database 'rovers', signing in as root user with no password 
$mysqli = new mysqli('localhost', 'root', '','rovers');
if ($mysqli->connect_error) {
	die('Connect Error (' . $mysqli->connect_errno . ') '
		. $mysqli->connect_error);
}
	
//find the stored contacts, order them by name 
$sql="SELECT * FROM `hasme` ORDER BY Name"; //make sure you change `hasme` to the name of your sql table 
$result=mysqli_query($mysqli,$sql);
	
//return all contacts as an array, pass that array to batch create function
$contactsArray = mysqli_fetch_all($result, MYSQLI_ASSOC);
$numCreated = ContactFactory::batchCreate($contactsArray, $groupId);

// Free result set
mysqli_free_result($result);

//close connection
mysqli_close($mysqli);

//confirm contact creation and link to user's contacts page 
echo $numCreated . " contacts were successfully created. <br/>";
echo "Click <a href='http://contacts.google.com'>here</a> to check your contacts. <br/>";
  
}else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/google_people/redirect.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
?>
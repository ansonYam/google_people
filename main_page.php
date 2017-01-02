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
  
$groupId = ContactFactory::createGroup("Salsa");
//echo $groupId;

//open a connection to the local sql database 'rovers', signing in as root user with no password 
$mysqli = new mysqli('localhost', 'root', '','rovers');
if ($mysqli->connect_error) {
	die('Connect Error (' . $mysqli->connect_errno . ') '
		. $mysqli->connect_error);
}
	
//find the stored contacts, order them by name 
$sql="SELECT * FROM `hasme` ORDER BY Name"; //make sure you change `hasme` to the name of your sql table 
$result=mysqli_query($mysqli,$sql);
	
while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
	$name = $row["Name"];
	$phoneNumber = $row["Phone 1 - Value"];
	$email = $row["E-mail 1 - Value"];
	
	if($name && $phoneNumber && $email){
	//create a new contact for each row in the database
	ContactFactory::create($name, $phoneNumber, $email, $groupId); 
	}
	echo $name . "'s account was created. <br/><br/>";
	//unset($name); unset($phoneNumber); unset($email);
}

// Free result set
mysqli_free_result($result);

//close connection
mysqli_close($mysqli);


}else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/google_people/redirect.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
?>
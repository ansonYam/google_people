<?php
session_start(); 

//no access token, go back to start 
if(!isset($_SESSION['access_token'])){
	$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/google_people/index.php';
	header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
	exit;
}

require_once __DIR__ .'/vendor/autoload.php';
use rajeshtomjoe\googlecontacts\factories\ContactFactory;

/*some code to get all contacts currently on the gmail account, but we aren't worry about that for now

try{
$contacts = ContactFactory::getAll(); //TODO: remove distribution lists from "all contacts"
}catch(Exception $e){
	echo 'Caught exception: ' , $e->getMessage(); "\n";	  
}*/

/* here is an example of what the Contact object looks like with this library
  { [0]=> object(rajeshtomjoe\googlecontacts\objects\Contact)#28 (6) { 
  ["id"]=> string(84) "http://www.google.com/m8/feeds/contacts/name%40example.com/base/..." 
  ["name"]=> string(12) "Joe Fish" 
  ["selfURL"]=> string(85) "https://www.google.com/m8/feeds/contacts/name%40example.com/full/..." 
  ["editURL"]=> string(102) "https://www.google.com/m8/feeds/contacts/name%40example.com/full/.../..." 
  ["email"]=> array(1) { ["work"]=> string(26) "name@example.com" } 
  ["phoneNumber"]=> array(1) { ["mobile"]=> string(15) "+1-604-555-5555" } }
*/

//open a connection to the local sql database 'rovers', signing in as root user with no password 
$mysqli = new mysqli('localhost', 'root', '','rovers');
if ($mysqli->connect_error) {
	die('Connect Error (' . $mysqli->connect_errno . ') '
		. $mysqli->connect_error);
}
	
//find the stored contacts, order them by name 
$sql="SELECT * FROM `hasme` ORDER BY Name";
$result=mysqli_query($mysqli,$sql);
	
while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
	$name = $row["Name"];
	$phoneNumber = $row["Phone 1 - Value"];
	$email = $row["E-mail 1 - Value"];
	
	echo $name . "<br/>" . $phoneNumber . "<br/>" . $email . "<br/><br/>";
	//create a new contact for each row in the database
	//ContactFactory::create($name, $phoneNumber, $email); 
}

// Free result set
mysqli_free_result($result);

mysqli_close($mysqli);
?>


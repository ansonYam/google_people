<?php 
require_once __DIR__ . '/vendor/autoload.php';

session_start();

//configure a new client specifying the scope of what you want to access (contacts, google drive...)
$client = new Google_Client();
$client->setAuthConfig('client_secret.json'); //downloaded client secret, keep it away from server
$client->addScope('http://www.google.com/m8/feeds/');

//add ?logout to the end of the url to revoke access token 
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}

//request authorization from user 
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  
  try{
  //$selfURL = 'http://www.google.com/m8/feeds/contacts/example%40gmail.com/base/...'; //this needs to be fed in somehow
  $contacts = rajeshtomjoe\googlecontacts\factories\ContactFactory::getAll();
  //var_dump($contacts);
  
  /* here is an example of what the Contact object looks like with this library
  { [0]=> object(rajeshtomjoe\googlecontacts\objects\Contact)#28 (6) { 
  ["id"]=> string(84) "http://www.google.com/m8/feeds/contacts/name%40example.com/base/..." 
  ["name"]=> string(12) "Joe Fish" 
  ["selfURL"]=> string(85) "https://www.google.com/m8/feeds/contacts/name%40example.com/full/..." 
  ["editURL"]=> string(102) "https://www.google.com/m8/feeds/contacts/name%40example.com/full/.../..." 
  ["email"]=> array(1) { ["work"]=> string(26) "name@example.com" } 
  ["phoneNumber"]=> array(1) { ["mobile"]=> string(15) "+1-604-555-5555" } }
  */
  
  foreach ($contacts as $contact){  
  //TODO: figure out how to handle error when phone number does not exist? 
  //should also remove distribution lists from "all contacts"
  
  /*
  Notice: Undefined property: rajeshtomjoe\googlecontacts\objects\Contact::$phoneNumber in C:\xampp\htdocs\google_people\index.php on line 41

  Warning: Invalid argument supplied for foreach() in C:\xampp\htdocs\google_people\index.php on line 41 
  https://www.google.com/m8/feeds/contacts/name%40example.com/full/...
  */
	  echo $contact->name . "<br>";
	  foreach ($contact->email as $key=>$value){
		  echo $key . ": " . $value . "<br>";
	  }
	  foreach ($contact->phoneNumber as $key=>$value){
		  echo $key . ": " . $value . "<br>";
	  }
	  echo $contact->selfURL . "<br>";
	  echo "<br>";
  }
  
  
  //update('https://www.google.com/m8/feeds/contacts/name%40example.com/full/...', '778-000-6666', 'new.name@example.com');
  
  }catch(Exception $e){
	  echo 'Caught exception: ' , $e->getMessage(); "\n";	  
  }


}else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/google_people/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

function update($selfURL, $phoneNumber, $email){
	$contact = rajeshtomjoe\googlecontacts\factories\ContactFactory::getBySelfURL($selfURL);

	var_dump($contact);

	$contact->phoneNumber = $phoneNumber;
	$contact->email = $email;

	$contactAfterUpdate = rajeshtomjoe\googlecontacts\factories\ContactFactory::submitUpdates($contact);

	var_dump($contactAfterUpdate);

}

?>

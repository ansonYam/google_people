<?php
session_start();

//add ?logout to the end of the url to revoke access token 
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>:^)</title>
  Welcome to contact sync page
  </head>
  <body>
  <p>Welcome to the contact sync page of 180th Scouts group. By accepting the terms and conditions you allow <br>
  the pccrovers.com to access your gmail account and make changes in the contact list of your account.</p>
  <div>
    <a href="http://www.pccrovers.com" style="float: left">
	 <button>I Do Not Agree</button>
    </a>
    <form action="main_page.php" style="float: left">
      <button>I Agree</button>
    </form>
  </div>	
  </body>
</html>


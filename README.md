The beginnings of a project to update a Google user's contact list according to a database, using XAMPP (https://www.apachefriends.org/index.html).    

#Dependencies
We are using this library that has a function to create contacts:  
https://github.com/rajeshtomjoe/php-google-contacts-v3-api.  

Download composer to handle your dependencies for you, including the above library and the Google API Client for php.
This just means  
1. Download the "composer.json" and "composer.lock" files from this repository to C:\xampp\htdocs\your_folder  
2. In your command prompt, change the working directory to the same location.        
3. Run the command "composer install", which should generate a "vendor" file with libraries.   

I've made some modifications to "ContactFactory.php" and "GoogleHelper.php", you will need to replace the files of the same name under 
C:\xampp\htdocs\...\vendor\rajeshtomjoe\php-google-contacts-v3-api in order to call batchCreate() or createGroup().   

#Database
We're also going to need a SQL database of contacts to import from, so upload your csv file into a database in localhost/phpmyadmin. You'll have to adjust the following lines when you make your sql connection in test.php:  
`$mysqli = new mysqli('localhost', 'root', '','rovers');`  
`$sql="SELECT * FROM `hasme` ORDER BY Name";`

#Credentials
Head over to https://console.developers.google.com/apis/credentials, make a new project, and download the client secret, and client id. 
Save those files (client_secret.json and client_id.json) under C:\xampp\htdocs\... as well, and you can point to them in your code without exposing your secret. Make sure to not commit these files.  You also need to set your allowed "redirect uri"s in the API console (these are the pages that your user can be redirected to).  
`$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/google_people/test.php';    
 header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));`   
If you don't set localhost/.../test.php as an authorised redirect uri, you will be unable to go there. 

#Troubleshooting
You may have to download https://curl.haxx.se/ca/cacert.pem and adjust your php.ini file accordingly. 

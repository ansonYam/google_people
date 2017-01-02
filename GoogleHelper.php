<?php

namespace rajeshtomjoe\googlecontacts\helpers;

abstract class GoogleHelper
{
    private static function loadConfig()
    {
        $contents = file_get_contents(__DIR__.'/../.config.json');

        $config = json_decode($contents);

        return $config;
    }

    public static function getClient()
    {
        $config = self::loadConfig();

        $client = new \Google_Client();

        $client->setApplicationName('Rapid Web Google Contacts API');

        $client->setScopes(array(/*
        'https://apps-apis.google.com/a/feeds/groups/',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://apps-apis.google.com/a/feeds/alias/',
        'https://apps-apis.google.com/a/feeds/user/',*/
        'https://www.googleapis.com/auth/contacts.readonly',
        'https://www.google.com/m8/feeds/',
        /*'https://www.google.com/m8/feeds/user/',*/
        ));

        $redirectUri = self::getBaseUrl().'/redirect-handler.php';

        $client->setClientId($config->clientID);
        $client->setClientSecret($config->clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setDeveloperKey($config->developerKey);

        if (isset($_SESSION['access_token'])) {
            $client->setAccessToken($_SESSION['access_token']);
        }

        return $client;
    }

    public static function getAuthUrl(\Google_Client $client)
    {
        return $client->createAuthUrl();
    }

    public static function authenticate(\Google_Client $client, $code)
    {
        return $client->fetchAccessTokenWithAuthCode($code);
    }

    public static function getAccessToken(\Google_Client $client)
    {
        return json_encode($client->getAccessToken());
    }

    public static function getBaseUrl(){
        // output: /myproject/index.php
        $currentPath = $_SERVER['PHP_SELF']; 

        // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index ) 
        $pathInfo = pathinfo($currentPath); 

        // output: localhost
        $hostName = $_SERVER['HTTP_HOST']; 

        // output: http://
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';

        // return: http://localhost/myproject/
        return $protocol.$hostName.$pathInfo['dirname'];
    }

    public static function getResponse($method,$url,$body = null){
        $client = self::getClient();

        $httpClient = new \GuzzleHttp\Client();
        $httpClient = $client->authorize($httpClient);
        $options = ['headers' => ['Content-Type' => 'application/atom+xml; charset=UTF-8; type=feed', 'GData-Version' => '3.0']];
        if(isset($body))
        {
            $options['body'] = $body;
        }
        $responseObj = $httpClient->request($method,$url,$options);  
        $response = $responseObj->getBody()->getContents();

        return $response;
    }
}
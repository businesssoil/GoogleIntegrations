<?php

require_once('oauth_config.php');

$accessTokenUri = AUTHORIZATION_ENDPOINT . "?client_id=" . CLIENT_ID . "&redirect_uri=" . CALLBACK_URI . "&scope=https://www.googleapis.com/auth/calendar" . "&response_type=code";

$token = $_COOKIE["token"];

if(isset($token)) {
    header("Location: oauth_callback_google.php");
} else {
    header("Location:".$accessTokenUri);
}

exit();
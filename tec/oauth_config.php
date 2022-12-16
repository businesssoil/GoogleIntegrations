<?php

require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('AUTHORIZATION_ENDPOINT', 'https://accounts.google.com/o/oauth2/auth');
define('ACCESS_TOKEN_ENDPOINT', 'https://accounts.google.com/o/oauth2/token');
define('CLIENT_ID', '527360843905-c0q8iu9nfl5b6j1gg8i7ivdnclousjjj.apps.googleusercontent.com');
define('CLIENT_SECRET', 'KXlS6djH-Kzhpuecs3v8CdTM');
define('CALLBACK_URI', 'http://contacts.huroncounty.com/tec/oauth_callback_google.php');

function GoogleDateFromString($str_date, $modify = null)
{
    $gdate = new Google_Service_Calendar_EventDateTime();
    $date = new DateTime($str_date);

    if($modify != null)
    {
        $date->modify($modify);
    }

    $gdate->setDate($date->format('Y-m-d'));

    return $gdate;
}
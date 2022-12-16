<?php

if(isset($_POST["calendarId"]) == false || isset($_POST['token']) == false || isset($_POST["title"]) == false)
{
    header("Location: index.php");
}

require_once('oauth_config.php');
require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

$calendarId = $_POST["calendarId"];
$start_date = strtotime($_POST["start_date"]);
$person_name = $_POST["person_name"];
$num_of_years = $_POST["num_of_years"];

$client = new Google_Client();
$cal = new Google_Service_Calendar($client);

$client->setAccessToken($_POST['token']);

for($i = 0; $i < $num_of_years; $i++)
{
    $title = "$person_name start date";

    if($i > 0)
    {
        $title = "$person_name $i" . ordinal_suffix($i) . " Anniv";
    }

    $predate = date("c", strtotime("+$i year", $start_date));
    $date = explode("T", $predate)[0];
    $event = new Google_Service_Calendar_Event();

    $event->setSummary($title);

    $event->setStart(GoogleDateFromString($date));
    $event->setEnd(GoogleDateFromString($date, '+1 day'));

    $createdEvent = $cal->events->insert($calendarId, $event);
}

function ordinal_suffix($num){
    $num = $num % 100; // protect against large numbers
    if($num < 11 || $num > 13){
        switch($num % 10){
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
        }
    }
    return 'th';
}

header("Location: index.php");

?>
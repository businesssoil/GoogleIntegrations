<?php

    if(isset($_POST["calendarId"]) == false || isset($_POST['token']) == false || isset($_POST["summary"]) == false)
    {
        header("Location: index.php");
    }

    require_once('oauth_config.php');
    require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

    $calendarId = $_POST["calendarId"];
    $dates = $_POST["dates"];
    $description = $_POST["description"];
    $token = $_POST['token'];
    $summary = $_POST["summary"];

    $client = new Google_Client();
    $cal = new Google_Service_Calendar($client);

    $client->setAccessToken($token);
    $client->setScopes(array(
        'https://www.googleapis.com/auth/calendar',
    ));

    for($i = 0; $i < count($dates); $i++)
    {
        $event = new Google_Service_Calendar_Event();

        if(isset($description))
        {
            $event->setDescription($description);
        }

        $event->setSummary($summary);
        $event->setStart(GoogleDateFromString($dates[$i]));
        $event->setEnd(GoogleDateFromString($dates[$i], '+1 day'));

        $createdEvent = $cal->events->insert($_POST["calendarId"], $event);
    }

    header("Location: index.php");
?>
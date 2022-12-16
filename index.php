<?php
header("Access-Control-Allow-Origin: *");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isset($_GET["group"])) {
    echo json_encode(array("error" => "You must supply a group!"));

    exit;
} 

$groupId = $_GET["group"];

session_start();

require_once realpath(dirname(__FILE__) . '/src/Google/autoload.php');

$client_id = '39001181540-chp5qto0o6rvsbv0nmu40diuf5i1j91r.apps.googleusercontent.com'; //Client ID
$service_account_name = '39001181540-chp5qto0o6rvsbv0nmu40diuf5i1j91r@developer.gserviceaccount.com'; //Email Address
$key_file_location = 'Contacts-d260eb0b1145.p12'; //key.p12

if (strpos($client_id, "googleusercontent") == false
    || !strlen($service_account_name)
    || !strlen($key_file_location)) {
  echo missingServiceAccountDetailsWarning();
  exit;
}

$client = new Google_Client();
$client->setApplicationName("Contacts");

if (isset($_SESSION['service_token'])) {
  $client->setAccessToken($_SESSION['service_token']);
}

$key = file_get_contents($key_file_location);
$cred = new Google_Auth_AssertionCredentials(
    $service_account_name,
    array('https://www.google.com/m8/feeds'),
    $key,
    'notasecret',
    'http://oauth.net/grant_type/jwt/1.0/bearer',
    'info@huroncounty.com'
);

$client->setAssertionCredentials($cred);

if ($client->getAuth()->isAccessTokenExpired()) {
  $client->getAuth()->refreshTokenWithAssertion($cred);
}

$access_token = json_decode($client->getAccessToken())->access_token;

$members = json_decode(file_get_contents("https://people.googleapis.com/v1/contactGroups/$groupId?groupFields=name&maxMembers=50&oauth_token=$access_token"));

$url2 = "https://people.googleapis.com/v1/people:batchGet?oauth_token=$access_token&personFields=organizations,phoneNumbers,addresses,urls,photos,biographies,memberships";

foreach($members->memberResourceNames as $member) {
    $url2 = $url2 . "&resourceNames=$member";
}

// echo($access_token);

$people = file_get_contents($url2);
$peopleJson = json_decode($people);
$contacts = [];

foreach($peopleJson->responses as $response) {
    $name = null;
    $phone = null;
    $address = null;
    $website = null;
    $description = null;
    $image = null;
    $memberships = [];
    
    foreach($response->person->memberships as $membership) {
        array_push($memberships, $membership->contactGroupMembership->contactGroupResourceName);
    }
    
    if(in_array("contactGroups/3c7b13958d347060", $memberships) == false) {
        continue;
    }
    
    if(isset($response->person) == false || 
      isset($response->person->organizations) == false ||
      count($response->person->organizations) == 0) {
        continue;
    } else {
        $name = $response->person->organizations[0]->name;
    }
    
    // Phone
    if(isset($response->person->phoneNumbers) && count($response->person->phoneNumbers) > 0 && isset($response->person->phoneNumbers[0]->value)) {
        $phone = $response->person->phoneNumbers[0]->value;
    }
    
    // Address
    if(isset($response->person->addresses) && count($response->person->addresses) > 0 && isset($response->person->addresses[0]->formattedValue)) {
        $address = $response->person->addresses[0]->formattedValue;
    }
    
    // Website
    if(isset($response->person->urls) && count($response->person->urls) > 0 && isset($response->person->urls[0]->value)) {
        $website = $response->person->urls[0]->value;
    }
    
    // Description
    if(isset($response->person->biographies) && count($response->person->biographies) > 0 && isset($response->person->biographies[0]->value)) {
        $description = $response->person->biographies[0]->value;
    }
    
    // Image 
    if(isset($response->person->photos) && count($response->person->photos) > 0 && isset($response->person->photos[0]->url)) {
        $image = $response->person->photos[0]->url;
    }
    
    $contact = new Contact($name, $phone, $address, $website, $description, $image);

    array_push($contacts, $contact);
}

usort($contacts, function($a, $b)
{
    return strcmp($a->name, $b->name);
});

echo(json_encode($contacts));

class Contact {
    public function __construct($_name, $_phone, $_address, $_website, $_description, $_image) {
        if($_phone == null) {
            $_phone = "&nbsp;";
        }

        if($_address == null) {
            $_address = "&nbsp;";
        }

        if($_website == null) {
            $_website = "&nbsp;";
        }
        else if(strpos($_website, "http://") === FALSE && strpos($_website, "https://") === FALSE) {
            $_website = "http://" . $_website;
        }

        if($_description == null) {
            $_description = "&nbsp;";
        }

        $this->name = $_name;
        $this->phone = $_phone;
        $this->address = $_address;
        $this->website = $_website;
        $this->description = $_description;
        $this->image = $_image;
    }

    public $name;
    public $phone;
    public $address;
    public $description;
    public $website;
    public $image;
}
<?php
header("Access-Control-Allow-Origin: *");

$searchGroup = "City Government";
$searchCity = "Bad Axe";

session_start();

require_once '../src/Google/autoload.php';

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

$groupId = "http://www.google.com/m8/feeds/groups/info%40huroncounty.com/base/3c7b13958d347060";
$huronId = null;
$gid2 = null;

//$groups = json_decode(file_get_contents('https://www.google.com/m8/feeds/groups/info@huroncounty.com/full?group=&max-results=1000&alt=json&v=3.0&oauth_token=' . $access_token));

//foreach($groups->feed->entry as $group) {
//    $title = $group->title->{'$t'};

//    if(strpos($title, "# Huron County Business") !== FALSE) {
//        $huronId = $group->id->{'$t'};
//    }
//}

$query = urlencode('Bad Axe Huron');
$url = 'https://www.google.com/m8/feeds/contacts/info@huroncounty.com/full?q=' . $query . ' &max-results=5000&alt=json&v=3.0&oauth_token=' . $access_token;

//$response =  json_decode(file_get_contents('https://www.google.com/m8/feeds/contacts/info@huroncounty.com/full?q=$query&max-results=5000&alt=json&v=3.0&oauth_token=' . $access_token));
$response =  json_decode(file_get_contents($url));

$cnt = count($response->feed->entry);
$contacts = [];

if(!isset($response->feed) || !isset($response->feed->entry) || !is_array($response->feed->entry)) {
    echo json_encode([]);

    exit;
}

echo("<ol>");
foreach($response->feed->entry as $contact) {
    $a = $contact->{'gd$structuredPostalAddress'};
    $b = $contact->{'gd$structuredPostalAddress'}[0];
    $c = $contact->{'gd$structuredPostalAddress'}[0]->{'gd$city'};
    $d = $contact->{'gd$structuredPostalAddress'}[0]->{'gd$city'}->{'$t'};

    if(!isset($contact->{'gd$structuredPostalAddress'}) || !isset($contact->{'gd$structuredPostalAddress'}[0]) || !isset($contact->{'gd$structuredPostalAddress'}[0]->{'gd$city'}) || !isset($contact->{'gd$structuredPostalAddress'}[0]->{'gd$city'}->{'$t'}))
    {
        continue;
    }

    echo("<li>");
    echo($contact->{'gd$organization'}[0]->{'gd$orgName'}->{'$t'});
    echo(" - ");
    echo($contact->{'gd$structuredPostalAddress'}[0]->{'gd$city'}->{'$t'});
    echo("</li>");

    //$contactGroups = $contact->{'gContact$groupMembershipInfo'};
    //$city = $contact->{'gd$structuredPostalAddress'}[0]->{'gd$city'}->{'$t'};

    //foreach($contactGroups as $group) {
    //    if(strpos($group->href, $huronId) !== FALSE && strpos($city, $searchCity) !== FALSE) {

    //        $name = $contact->{'gd$organization'}[0]->{'gd$orgName'}->{'$t'};
    //        $phone = $contact->{'gd$phoneNumber'}[0]->{'$t'};
    //        $address = $contact->{'gd$structuredPostalAddress'}[0]->{'gd$formattedAddress'}->{'$t'};
    //        $website = $contact->{'gContact$website'}[0]->href;
    //        $description = $contact->content->{'$t'};

    //        array_push($contacts, new Contact($name, $phone, $address, $website, $description, $image));
    //    }
    //}
}

echo("</ol>");
usort($contacts, cmp);

echo json_encode($contacts);

function cmp($a, $b)
{
    return strcmp($a->name, $b->name);
}

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
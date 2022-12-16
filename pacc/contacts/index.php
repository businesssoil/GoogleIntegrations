<?php
header("Access-Control-Allow-Origin: *");

if(isset($_GET['group']) == false)
{
    echo json_decode([]);

    exit;
}

require_once realpath(dirname(__FILE__) . '/../../src/Google/autoload.php');

$service_account_name = 'google-apis@seraphic-rarity-177623.iam.gserviceaccount.com'; //Email Address
$key_file_location = '../GoogleApis-1f272bcc620a.p12'; //key.p12
$client = new Google_Client();
$client->setApplicationName("GoogleApis");
$key = file_get_contents($key_file_location);
$type = array('https://www.google.com/m8/feeds');
$pass = 'notasecret';
$bearer = 'http://oauth.net/grant_type/jwt/1.0/bearer';
$impersonate = 'chamber@portaustinarea.com';
$cred = new Google_Auth_AssertionCredentials($service_account_name, $type, $key, $pass, $bearer, $impersonate);

$client->setAssertionCredentials($cred);

if ($client->getAuth()->isAccessTokenExpired()) {
  $client->getAuth()->refreshTokenWithAssertion($cred);
}

$access_token = json_decode($client->getAccessToken())->access_token;
$groupId = null;
$groups = json_decode(file_get_contents("https://" . "www.google.com/m8/feeds/groups/$impersonate/full?group=&max-results=1000&alt=json&v=3.0&oauth_token=$access_token"));
$search = $_GET['group'];

foreach($groups->feed->entry as $group) {
    $title = $group->title->{'$t'};

    if(strpos($title, $search) !== FALSE) {
        $groupId = $group->id->{'$t'};
    }
}

$response =  json_decode(file_get_contents("https://" . "www.google.com/m8/feeds/contacts/$impersonate/full?group=$groupId&max-results=5000&alt=json&v=3.0&oauth_token=$access_token"));
$contacts = [];

if(!isset($response->feed) || !isset($response->feed->entry) || !is_array($response->feed->entry)) {
    echo json_encode([]);

    exit;
}

foreach($response->feed->entry as $contact) {
    $name = $contact->{'gd$name'}->{'gd$fullName'}->{'$t'};
    $org = $contact->{'gd$organization'}[0]->{'gd$orgName'}->{'$t'};
    $phone = $contact->{'gd$phoneNumber'}[0]->{'$t'};
    $address = $contact->{'gd$structuredPostalAddress'}[0]->{'gd$formattedAddress'}->{'$t'};
    $website = $contact->{'gContact$website'}[0]->href;
    $description = $contact->content->{'$t'};

    foreach($contact->link as $link) {
        if(strpos($link->rel, "rel#photo") !== FALSE && isset($link->href)) {
            $image = $link->href . '&oauth_token=' . $access_token;
        }
    }

    array_push($contacts, new Contact($org, $phone, $address, $website, $description, $image));
}

usort($contacts, cmp);

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

    function toHtml() {
        $image = "";

        if($this->image != null)
        {
            $image = "<img src='{$this->image}' />";
        }

        return "<div class='row sqs-row'>
                    <div class='col sqs-col-3 span-3'>
                        <div class='sqs-block image-block sqs-block-image sqs-text-ready'>
                    <div class='sqs-block-content'>
                        <div class='image-block-outer-wrapper layout-caption-hidden design-layout-inline sqs-narrow-width'>
                            <div class='intrinsic' style='max-width:2500.0px;'>
                                <div style='padding-bottom: 90.4762%; overflow: hidden;' class='image-block-wrapper   has-aspect-ratio'>
                                    {$image}
                                </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>
                <div class='col sqs-col-9 span-9'>
                    <div class='sqs-block html-block sqs-block-html'>
                        <div class='sqs-block-content'>
                            <h2>{$this->name}</h2>
                            <p>{$this->address}</p>
                            <p>{$this->phone}</p>
                            <h3><a target='_blank' href='{$this->website}'>Website</a></h3>
                        </div>
                    </div>
                </div>
                </div>
                <div class='sqs-block html-block sqs-block-html'>
                    <div class='sqs-block-content'>
                        <p>{$this->description}</p>
                    </div>
                </div>";
    }

    public $name;
    public $phone;
    public $address;
    public $description;
    public $website;
    public $image;
}


foreach($contacts as $contact) {
    echo ($contact->toHtml());
}
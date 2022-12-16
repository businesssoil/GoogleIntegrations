<?php
header("Access-Control-Allow-Origin: *");

if(isset($_GET['start']) == false || isset($_GET["calendar_type"]) == false)
{
    echo json_encode(array("error" => "You must set a start and type parameter."));

    exit;
}

// standby-list

// vacation-conference-scheule
// tecmi.coop_4v1ae113a4q529ad1im7lsigjg@group.calendar.google.com

// private-calendars

// dates-to-remember

//https://spunmonkey.com/display-contents-google-calendar-php/

require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

$start = $_GET["start"] . "T00:00:00-00:00";
$end = $_GET["end"] . "T00:00:00-00:00";
$calendar_type = $_GET["calendar_type"];

$client = new Google_Client();
$client->setApplicationName("Thumb Electric Website");
$client->setDeveloperKey('AIzaSyDpX7Gluf8vYgacc4Tw5kj2m8Hsk3qn0bE');
$cal = new Google_Service_Calendar($client);
$params = array('timeMin' => $start, 'timeMax' => $end);
$calTimeZone = $events->timeZone;
$results = [];

date_default_timezone_set($calTimeZone);

$calendars = get_calendars($calendar_type);

foreach ($calendars as $calendar) {
    $events = $cal->events->listEvents($calendar->id, $params);
    $gevents = $events->getItems();

    foreach ($gevents as $event) {
        if(is_array($event->recurrence)) {
            $instances = $cal->events->instances($calendar->id, $event->id, $params);

            foreach($instances->getItems() as $instance) {
                array_push($results, new Result($instance->summary, $instance->start, $instance->end, $calendar->color));
            }
        } else {
            array_push($results, new Result($event->summary, $event->start, $event->end, $calendar->color));
        }
    }
}
echo json_encode($results);

/**
 * Summary of get_calendars
 * @param string $type
 * @return Calendar[]
 */
function get_calendars($type) {
    $colors = ["#616161","#33B679","#D50000","#EF6C00","#3F51B5","#4285F4","#C0CA33","#E4C441","#009688","#7CB342","#D50000","#AD1457","#B39DDB","#0B8043","#F4511E","#7CB342","#C0CA33","#7986CB","#C0CA33","#c8be8e","#E67C73"];
    $result = [
        new Calendar("tecmi.coop_bre8tvt32obnsng4j43kosnie4@group.calendar.google.com", $colors[0], "none"),
        new Calendar("tecmi.coop_qd17tcealre584ppb0ots8u9jg@group.calendar.google.com", $colors[1], "none"),

        new Calendar("tecmi.coop_pk8f0j6kbdb9h0l9v5eip6d2h0@group.calendar.google.com", $colors[0], "standby"),
        new Calendar("tecmi.coop_0c3jf2kg8ca5v2ptl79mparsbc@group.calendar.google.com", $colors[1], "standby"),
        new Calendar("tecmi.coop_6obhu9ql98ero80ubmdtr8em7o@group.calendar.google.com", $colors[7], "standby"),

        new Calendar("tecmi.coop_l8occeng58c87da69ks46pp0tg@group.calendar.google.com", $colors[0], "vacation"),
        new Calendar("tecmi.coop_v564ro79c25fiivdpl4kutclf4@group.calendar.google.com", $colors[1], "vacation"),
        new Calendar("tecmi.coop_4v1ae113a4q529ad1im7lsigjg@group.calendar.google.com", $colors[2], "vacation"),
        new Calendar("tecmi.coop_q3aeku1i2gauhtnm7eeov8pp9o@group.calendar.google.com", $colors[3], "vacation"),
        new Calendar("tecmi.coop_369h8fs6bn2nge1lodlne0vkoo@group.calendar.google.com", $colors[4], "vacation"),
        new Calendar("tecmi.coop_0q3uq81r6125dnunrcdnsafaso@group.calendar.google.com", $colors[5], "vacation"),
        new Calendar("tecmi.coop_rtos48rjrdljjoi0rsp5b6rncs@group.calendar.google.com", $colors[6], "vacation"),
        new Calendar("tecmi.coop_6obhu9ql98ero80ubmdtr8em7o@group.calendar.google.com", $colors[7], "vacation"),

        new Calendar("tecmi.coop_5dpcqgtbij9380vmjkdger5908@group.calendar.google.com", $colors[2], "remember"),
        new Calendar("tecmi.coop_m4fmstmeu8ernk2h6e28htdnqg@group.calendar.google.com", $colors[0], "remember"),
        new Calendar("tecmi.coop_qr57oluj85nvetpp0gdogf0e54@group.calendar.google.com", $colors[1], "remember"),
        new Calendar("tecmi.coop_r53fsi68b11eo2vurr2a1v6tuc@group.calendar.google.com", $colors[3], "remember"),
        new Calendar("tecmi.coop_t06anhp55gm1s33griu1ebcq4g@group.calendar.google.com", $colors[4], "remember"),
        new Calendar("tecmi.coop_8vnnn2fdn9mms0cvqmkonobe70@group.calendar.google.com", $colors[5], "remember"),
        new Calendar("tecmi.coop_q3aeku1i2gauhtnm7eeov8pp9o@group.calendar.google.com", $colors[6], "remember"),
        new Calendar("tecmi.coop_6e5he9vltf434u88uvaqokktek@group.calendar.google.com", $colors[7], "remember"),

        new Calendar("tecmi.coop_l8occeng58c87da69ks46pp0tg@group.calendar.google.com", $colors[0], "union_vacation"),
        new Calendar("tecmi.coop_v564ro79c25fiivdpl4kutclf4@group.calendar.google.com", $colors[1], "union_vacation"),
        new Calendar("tecmi.coop_q3aeku1i2gauhtnm7eeov8pp9o@group.calendar.google.com", $colors[2], "union_vacation"),

        new Calendar("tecmi.coop_l8occeng58c87da69ks46pp0tg@group.calendar.google.com", $colors[0], "union_vacation_um"),
        new Calendar("tecmi.coop_v564ro79c25fiivdpl4kutclf4@group.calendar.google.com", $colors[1], "union_vacation_um"),
        new Calendar("tecmi.coop_4v1ae113a4q529ad1im7lsigjg@group.calendar.google.com", $colors[2], "union_vacation_um"),
        new Calendar("tecmi.coop_q3aeku1i2gauhtnm7eeov8pp9o@group.calendar.google.com", $colors[3], "union_vacation_um"),

        new Calendar("tecmi.coop_l8occeng58c87da69ks46pp0tg@group.calendar.google.com", $colors[0], "member-services"),
        new Calendar("tecmi.coop_q3aeku1i2gauhtnm7eeov8pp9o@group.calendar.google.com", $colors[1], "member-services"),

        new Calendar("tecmi.coop_v564ro79c25fiivdpl4kutclf4@group.calendar.google.com", $colors[0], "vacation-operations"),
        new Calendar("tecmi.coop_q3aeku1i2gauhtnm7eeov8pp9o@group.calendar.google.com", $colors[1], "vacation-operations"),
    ];

    return array_filter($result, function($value) use($type) {
        if($value->calendar_type == $type){
            return true;
        }

        return false;
    });
}

class Calendar {
    public function __construct($_id, $_color, $_type) {
        $this->id = $_id;
        $this->color = $_color;
        $this->calendar_type = $_type;
    }

    public $id;
    public $color;
    public $calendar_type;
}

class Result {
    public function __construct($_title, $_start, $_end, $_color) {
        $this->title = $_title;

        $startStr = $_start->dateTime;

        if(empty($startStr)) {
            $startStr = $_start->date;
        }

        $endStr = $_end->dateTime;

        if(empty($endStr)) {
            $endStr = $_end->date;
        }

        $this->start = $startStr;
        $this->end = $endStr;
        $this->color = $_color;
    }

    public $end;
    public $start;
    public $title;
    public $color;
}

?>
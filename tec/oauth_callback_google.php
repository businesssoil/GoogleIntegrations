<?php

require_once('oauth_config.php');

$client = new Google_Client();

$token;

if(isset($_COOKIE["token"]) == false && isset($_GET['code']) == true)
{
    $code = $_GET['code'];

    $client->setApplicationName('API Project');
    $client->setClientId(CLIENT_ID);
    $client->setClientSecret(CLIENT_SECRET);
    $client->setRedirectUri(CALLBACK_URI);
    $client->setDeveloperKey('AIzaSyA_fhuHQZt4hC57kvwMJQqp3d8CZAtlUac');
    $client->authenticate($code);

    $token = $client->getAccessToken();
    
    setcookie("token", $token, time() + (60 * 30));
} else if(isset($_COOKIE["token"]) == false && isset($_GET['code']) == false) {
    header("Location: index.php");
} else {
    $token = $_COOKIE["token"];
}

$client->setAccessToken($token);

$service = new Google_Service_Calendar($client);

$calendarList = $service->calendarList->listCalendarList();
$calendars = $calendarList->getItems();
$calendar_dropdown = [];

foreach ($calendars as $calendar) {
    array_push($calendar_dropdown, new SelectListItem($calendar->getId(), $calendar->getSummary()));
}

usort($calendar_dropdown, function($a, $b) {
    return strcmp($a->text, $b->text);
});

class SelectListItem {
    public function __construct($_id, $_text) {
        $this->id = $_id;
        $this->text = $_text;
    }

    public $id;
    public $text;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Google Contacts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.min.css" />
</head>

<body>
    <nav class="navbar navbar-inverse">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">Business Soil</a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav pull-right">
                    <li><a href="http://contacts.huroncounty.com/tec/">Re-authenticate</a></li>
                    <li class="active"><a href="#">Event Inserter</a></li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>

    <div class="container">
        <!--
            http://localhost:64304/tec/insert_events.php
            http://localhost:64304/tec/insert_anniversaries.php
        -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Vacation</a></li>
            <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Anniversary</a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="home" style="padding-top:20px;">
                <form action="insert_vacation.php" method="post">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <div id="vacationCalendar"></div>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label>Summary</label>
                                <input class="form-control" name="summary" required />
                            </div>

                            <div class="form-group">
                                <label>Select a Calendar</label>
                                <select class="form-control" name="calendarId" required>
                                    <option></option>
                                    <?php
                                    for ($i = 0; $i < count($calendar_dropdown); $i++) {
                                        echo("<option value='" . $calendar_dropdown[$i]->id . "'>" . $calendar_dropdown[$i]->text . "</option>");
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description"></textarea>
                            </div>

                            <textarea style="display:none;" name="token" class="form-control" readonly="readonly"><?php echo($token); ?> </textarea>

                            <div style="display:none;" class="date-container"></div>
                        </div>
                    </div>

                    <hr />

                    <button type="submit" class="btn btn-success pull-right">Create</button>
                </form>
            </div>

            <div role="tabpanel" class="tab-pane" id="profile" style="padding-top:20px;">
                <form action="insert_anniversaries.php" method="post">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input class="form-control" id="aniversaryDate" />
                        <input type="hidden" name="start_date" />
                    </div>

                    <div class="form-group">
                        <label>Person Name</label>
                        <input class="form-control" name="person_name" required />
                    </div>

                    <hr />

                    <input type="hidden" name="num_of_years" value="50" />
                    <input type="hidden" name="calendarId" value="tecmi.coop_5dpcqgtbij9380vmjkdger5908@group.calendar.google.com" />
                    <textarea style="display:none;" name="token" class="form-control" readonly="readonly"><?php echo($token); ?> </textarea>

                    <button type="submit" class="btn btn-success pull-right">Create</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.min.js"></script>
    <script src="app.js"></script>
</body>
</html>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$resume = $_FILES["resume"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];

if(!$fname) {
    echo "You must supply a first name!";

    exit;
}

if(!$lname) {
    echo "You must supply a last name!";

    exit;
}

if(!$resume) {
    echo "You must supply a file!";

    exit;
}

include_once("../PHPMailer.php");

$email = new PHPMailer();

$email->From      = 'resume.upload@avci.com';
$email->FromName  = 'Resume Upload';
$email->Subject   = "Resume Submission: $fname $lname";
$email->Body = "Attached is the new resume that was submitted by $fname $lname.";
$email->AddAddress( 'HumanResources@avci.net' );

$email->AddAttachment( $resume['tmp_name'] , $resume['name'], 'base64', $resume['type'] );

$result = $email->Send();

if($result) {
    header('location: https://agrivalleycommunicationsinc.squarespace.com/thank-you');
} else {
    echo "Error sending resume. Please try again.";
}

?>
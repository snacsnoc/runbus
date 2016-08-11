<?php


if(!empty($_POST)){

require '../vendor/autoload.php';
require '../Sms_job.php';
require_once '../settings.php';


Resque::setBackend($settings['REDIS_BACKEND']);


  $minute = $_POST['minute'];
  $countdown = $_POST['countdown'];
  $route_no = $_POST['route_no'];
  $remind_time = $countdown - $minute;
  $phone_number = $_POST['phonenumber'];

if(!isset($_COOKIE['dest_phone_number'])) {
setcookie('dest_phone_number', $phone_number, time() + (86400 * 30), "/");
}

$args = array(
        'leave_time' => $remind_time,
        'departure' => $minute,
        'route_no' => $route_no,
        'phone_number' => $phone_number
        );

$seconds = $remind_time * 60;
ResqueScheduler::enqueueIn($seconds, 'test', 'Sms_job', $args);

$notice = "Sending in ".$remind_time." minutes!";
$return = array('success' => 1, 'response' => $notice);

echo json_encode($return);
}
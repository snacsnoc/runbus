<?php


if(!empty($_POST)){

require '../settings.php';
require '../vendor/autoload.php';
require '../Sms_job.php';



Resque::setBackend($settings['REDIS_BACKEND']);


  $minute = intval($_POST['minute']);
  $countdown = intval($_POST['countdown']);
  $route_no = $_POST['route_no'];
  

  $remind_time = $countdown - $minute;
if(preg_match("/^[0-9]{3}[0-9]{3}[0-9]{4}$/", $_POST['phonenumber'])) {
  // $phone is valid
  $phone_number = $_POST['phonenumber'];
}else{

  $notice = "Invalid phone number";
  $return = array('success' => 0, 'response' => $notice);

  echo json_encode($return);

  error_log(json_encode($return), 0);
  error_log('Invalid phone number!', 0);

  die();
}

//If the cookie phone number isn't set, set it now
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

//Schedule the SMS
//
//Seconds to send, queue ID, PHP class, data to send
ResqueScheduler::enqueueIn($seconds, 'test', 'Sms_job', $args);

$notice = "Sending in ".$remind_time." minutes!";
$return = array('success' => 1, 'response' => $notice);

echo json_encode($return);
error_log('SMS sent successfully!', 0);
}else{
  error_log('Error on SMS sending', 0);
  die('cant!');

}
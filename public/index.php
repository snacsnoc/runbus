<?php
#error_reporting(E_ALL);

#ini_set('display_errors',1);

$config['apikey'] = '4Q27nB1G2aGGzOheGNeg';

require '../vendor/autoload.php';
require '../Sms_job.php';
require_once '../settings.php';


define('APP_PATH', realpath('../'));

$loader = new Twig_Loader_Filesystem(APP_PATH.'/views');

$twig = new Twig_Environment($loader);

Resque::setBackend($settings['REDIS_BACKEND']);


$opts = array(
  'http'=>array(
    'method'=> "GET",
    'header'=> "Accept: application/JSON\r\n"
  )
);


$context = stream_context_create($opts);


//Use html5 geolocation to get the user's location
if(isset($_GET['lat']) && isset($_GET['long'])){

  //API only likes 6 numbers after decimal
  $geo['lat'] = substr($_GET['lat'], 0, 9);

  $geo['long'] = substr($_GET['long'], 0, 10);
                    
  $stops   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops?apikey='.$config['apikey'].'&lat='.$geo['lat'].'&long='.$geo['long'].'&radius=200', false, $context);
  $stops_output      = json_decode($stops);
  if(empty($stops_output)){
    echo 'no stops by geo';
  }

  $bus_time   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stops_output[0]->StopNo.'/estimates?apikey='.$config['apikey'].'&count=2&timeframe=65', false, $context);
  $bus_time_output      = json_decode($bus_time);



switch($bus_time_output[0]->Schedules[0]->ScheduleStatus){
     case "*":
          $bus_schedule = "On time";
          break;

     case "-":
          $bus_schedule = "Delay";
          break;

     case "+":
          $bus_schedule = "Fast";
          break;          
}



}
if(isset($_GET['countdown'])){
  $minute = $_GET['minute'];
  $countdown = $_GET['countdown'];
  $route_no = $_GET['route_no'];
  $remind_time = $countdown - $minute;



$args = array(
        'leave_time' => $remind_time,
        'departure' => $minute,
        'route_no' => $route_no
        );

$seconds = $remind_time * 60;
ResqueScheduler::enqueueIn($seconds, 'test', 'Sms_job', $args);
echo "Sending in ".$remind_time." minutes";
}

if(isset($_GET['address'])){
  $address     = rawurlencode($_GET['address']);
  $geocode     = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $address . '&sensor=false');
  $output      = json_decode($geocode);

  if($output->status == "ZERO_RESULTS"){
    die ('No results for address');
  }

  $geo['lat'] = substr($output->results[0]->geometry->location->lat, 0,-1);

  $geo['long'] = substr($output->results[0]->geometry->location->lng, 0,-1);

  $stops   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops?apikey='.$config['apikey'].'&lat='.$geo['lat'].'&long='.$geo['long'].'&radius=100', false, $context);
  $stops_output      = json_decode($stops);

  $bus_time   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stops_output[0]->StopNo.'/estimates?apikey='.$config['apikey'].'&count=2&timeframe=65', false, $context);
  $bus_time_output      = json_decode($bus_time);



switch($bus_time_output[0]->Schedules[0]->ScheduleStatus){
     case "*":
          $bus_schedule = "On time";
          break;

     case "-":
          $bus_schedule = "Delay";
          break;

     case "+":
          $bus_schedule = "Fast";
          break;          


}

}


if(isset($_GET['stopno'])){

  $stopnumber = trim($_GET['stopno']);
  $bus_time   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stopnumber.'/estimates?apikey='.$config['apikey'].'&count=2&timeframe=65', false, $context);
  $bus_time_output      = json_decode($bus_time);


  switch($bus_time_output[0]->Schedules[0]->ScheduleStatus){
     case "*":
          $bus_schedule = "On time";
          break;

     case "-":
          $bus_schedule = "Delay";
          break;

     case "+":
          $bus_schedule = "Fast";
          break;          


}
}


echo $twig->render('index.twig', array('closest_stop' => $stops_output[0]->StopNo,
    'stop_location' => $stops_output[0]->Name,
    'route_no' => $bus_time_output[0]->RouteNo,
    'next_estimate' => $bus_time_output[0]->Schedules,
    'bus_schedule_time' => $bus_schedule));


require 'compass.php';
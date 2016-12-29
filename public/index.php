<?php
//Config to display errors or not
if($config['debug_mode'] == TRUE){
   if(rmdir('./cache') == FALSE){
    echo "cannot remove cache directory!";
  }
  error_reporting(E_ALL);
  ini_set('display_errors',1);
}else{
  error_log('error display is OFF', 0);
}


require '../settings.php';
require '../vendor/autoload.php';
require '../Sms_job.php';




define('APP_PATH', realpath('../'));

$loader = new Twig_Loader_Filesystem(APP_PATH.'/views');


//Enable/disable caching of HTML views
if($config['cache_views'] == TRUE){
        $twig = new Twig_Environment($loader, array(
                    'cache' => APP_PATH.'/cache'));
        error_log('cache hit!', 0);
}else{
        $twig = new Twig_Environment($loader);
        error_log('cache MISS', 0);

}

Resque::setBackend($settings['REDIS_BACKEND']);

$opts = array(
  'http'=>array(
    'method'=> "GET",
    'header'=> "Accept: application/JSON\r\n"
  )
);


$context = stream_context_create($opts);


//Use html5 geolocation to get the user's location
if(!empty($_GET['lat']) && !empty($_GET['long'])){


  //API only likes 6 numbers after decimal
  $geo['lat'] = substr($_GET['lat'], 0, 9);

  $geo['long'] = substr($_GET['long'], 0, 10);
  //First we get the bus stops near our location                 
  $stops   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops?apikey='.$config['apikey'].'&lat='.$geo['lat'].'&long='.$geo['long'].'&radius=100', false, $context);
  $stops_output      = json_decode($stops);

  //If we get back failure from the HTTP request
  if($stops == FALSE){
    $notice = 'No stops near you within 100m, sorry! Try with an address instead';
    error_log('no stops near lat '.$geo['lat'].' long '.$geo['long'], 0);

  }else{

error_log($stops_output, 0);
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
}

if(isset($_GET['address'])){
  $address     = rawurlencode($_GET['address']);
  $geocode     = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $address . '&sensor=false');
  $output      = json_decode($geocode);

  if($output->status == "ZERO_RESULTS"){
    echo 'No results for address';
    error_log("No results for address: $address", 0);
  }

  //Check if we are searching Canada or not
  if($output->results[0]->address_components[5]->short_name != 'CA'){
     $notice = 'Wrong address, did you forget to add the city to the address?';

  }else{
          
          $geo['lat'] = substr($output->results[0]->geometry->location->lat, 0,-1);

          $geo['long'] = substr($output->results[0]->geometry->location->lng, 0,-1);

          $stops   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops?apikey='.$config['apikey'].'&lat='.$geo['lat'].'&long='.$geo['long'].'&radius=500', false, $context);
          $stops_output      = json_decode($stops);
          if($stops_output == NULL){
            $notice .= "No stops found, is the area too large? (Not specific enough)";
          }
          $bus_time   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stops_output[0]->StopNo.'/estimates?apikey='.$config['apikey'].'&count=2&timeframe=85', false, $context);
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
}


if(isset($_GET['stopno'])){

  $stopnumber = trim($_GET['stopno']);
  $bus_time   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stopnumber.'/estimates?apikey='.$config['apikey'].'&count=2&timeframe=95', false, $context);

  $bus_time_output      = json_decode($bus_time);

  if($bus_time_output == NULL){
    $notice .=  "Not a valid bus stop number or it's too early/late!";
  }
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
    'bus_schedule_time' => $bus_schedule,
    'dest_phone_number' => $_COOKIE['dest_phone_number'],
    'notice' => $notice));



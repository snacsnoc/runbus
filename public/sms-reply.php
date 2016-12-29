<?php
    require_once '../settings.php';
    $opts = array(
        'http'=>array(
        'method'=> "GET",
        'header'=> "Accept: application/JSON\r\n"
        )
    );
    /*
    error_reporting(E_ALL);
    ini_set('display_errors',1);
    */
    $context = stream_context_create($opts);

    $stopnumber = trim($_REQUEST['Body']);

    //Now greet the sender
    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  
  //Get departure times of a single bus stop, all routes
    if(is_numeric($stopnumber)){
    $bus_time   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stopnumber.'/estimates?apikey='.$config['apikey'].'&count=3&timeframe=90', false, $context);
    $bus_time_output      = json_decode($bus_time);   
  if($bus_time_output == NULL){
    ?>
    <Response>
    <Message>Sorry, not a valid bus stop number! Or maybe it is too early or too late...</Message>
    </Response>
<?php
   die();
  }
    //Time until the bus arives
    $countdown_time = $bus_time_output[0]->Schedules[0]->ExpectedCountdown;

    //If arrival time is less than 5 minutes  add a running person emoji
    if($countdown_time < 5){
        $countdown_time = $countdown_time." 🏃";
    }
?>
<Response>
    <Message>Bus leave time at <?=$bus_time_output[0]->Schedules[0]->ExpectedLeaveTime; ?> in <?= $countdown_time; ?> minutes. Next bus at <?=$bus_time_output[0]->Schedules[1]->ExpectedLeaveTime; ?> and <?=$bus_time_output[0]->Schedules[2]->ExpectedLeaveTime; ?></Message>
</Response>
<?php
//Get routes for a bus stop
//ex 50233 routes
}elseif(preg_match('/([0-9]{5}) (routes)/', $stopnumber, $bus_stop_number)){
    $stopnumber = $bus_stop_number[1];
     $bus_time   = file_get_contents('http://api.translink.ca/rttiapi/v1/routes?apikey='.$config['apikey'].'&stopNo='.$stopnumber, false, $context);
    $bus_routes      = json_decode($bus_time);   

    //If there is more than one route for a given stop
    //This should be fixed
    if($bus_routes[1]->RouteNo == TRUE){
        $other_route =  $bus_routes[1]->RouteNo .' - '. $bus_routes[1]->Name; 
    }
?>
<Response>
    <Message>Routes are <?=$bus_routes[0]->RouteNo; ?> -  <?=$bus_routes[0]->Name;  echo $other_route; ?></Message>
</Response>

<?php
//Get departure times of a single bus stop, filter by route number
//ex 50233 19

}elseif(preg_match('/([0-9]{5}) ([0-9]+)/', $stopnumber, $bus_stop_number)){
    $stopnumber = $bus_stop_number[1];
    $route_number = $bus_stop_number[2];
    $bus_time   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stopnumber.'/estimates?apikey='.$config['apikey'].'&count=3&timeframe=90&RouteNo='.$route_number, false, $context);
    $bus_time_output      = json_decode($bus_time);   

    $countdown_time = $bus_time_output[0]->Schedules[0]->ExpectedCountdown;

    //If arrival time is less than 5 minutes  add a running person emoji
    if($countdown_time < 5){
        $countdown_time = $countdown_time." 🏃";
    }
?>
<Response>
    <Message>Bus leave time at <?=$bus_time_output[0]->Schedules[0]->ExpectedLeaveTime; ?> in <?=$countdown_time; ?> minutes. Next bus at <?=$bus_time_output[0]->Schedules[1]->ExpectedLeaveTime; ?> and <?=$bus_time_output[0]->Schedules[2]->ExpectedLeaveTime; ?></Message>
</Response>

<?php
}else{
    ?>
    <Response>
    <Message>Sorry, not a valid stop/router number!  Use stop# or stop# routes or stop# route#</Message>
    </Response>

    <?php
    
}

?>

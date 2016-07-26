<?php
    require_once '../settings.php';
    $opts = array(
        'http'=>array(
        'method'=> "GET",
        'header'=> "Accept: application/JSON\r\n"
        )
    );

    $context = stream_context_create($opts);

    $stopnumber = trim($_REQUEST['Body']);

    $bus_time   = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stopnumber.'/estimates?apikey='.$config['apikey'].'&count=2&timeframe=60', false, $context);
    $bus_time_output      = json_decode($bus_time);   

    $countdown_time = $bus_time_output[0]->Schedules[0]->ExpectedCountdown;

    //If arrival time is less than 5 minutes  add a running person emoji
    if($countdown_time < 5){
        $countdown_time = $countdown_time."🏃";
    }
    // now greet the sender
    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <Message>Leave time at <?php echo $bus_time_output[0]->Schedules[0]->ExpectedLeaveTime; ?> in <?php echo $countdown_time; ?> minutes. Next bus at <?php echo $bus_time_output[0]->Schedules[1]->ExpectedLeaveTime; ?></Message>
</Response>

<?php
class Sms_job
{
    public function perform()
    {
        try {

			$client = new Services_Twilio($config['twilio_accountsid'], $config['twilio_authtoken']);

			$message = $client->account->messages->create(array(
    			"From" => "+16042395120", // From a valid Twilio number
    			"To" => "+1".$this->args['phone_number'],   // Text this number
    			"Body" => "Leave soon. Bus ".$this->args['route_no']." departs in ".$this->args['departure'],
			));

			// Display a confirmation message on the screen
			echo "Sent message {$message->sid}";

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
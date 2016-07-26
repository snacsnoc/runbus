<?php
//run with VERBOSE=1 php5 worker.php

require_once './settings.php';
require_once './Sms_job.php';
require './vendor/autoload.php';
require_once './vendor/chrisboulton/php-resque/resque.php';
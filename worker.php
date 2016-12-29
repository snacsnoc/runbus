<?php
//run with VERBOSE=1 php7 worker.php

require './settings.php';
require './Sms_job.php';
require './vendor/autoload.php';
require_once './vendor/chrisboulton/php-resque/resque.php';
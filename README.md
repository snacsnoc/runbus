# runbus

Vancouver bus information with SMS alerts. Text 604-239-5120 with bus stop number for bus arrival information. Alerts via SMS when bus is departing soon.

### Requirements 
Only requirements outside of standard PHP stack is Redis for the SMS queue. Run ``php7 scheduler.php`` for the scheduler, and ``php7 worker.php`` for the worker (PHP Resque).

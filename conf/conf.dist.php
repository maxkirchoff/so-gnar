<?php
return array(
    // array of services to watch
    'services'  => array(
        '{{service_name}}' => "{{service_url}}",
        ),
    // how often do you want to ping services
    'frequency'  =>  '{{frequency}}',
    // enable audio alerts
    'alerts'    => true,
    // set successful status alert wav or mp3 file path
    'alert_success' => '{{alert_success}}',
    // set failure status alert wav or mp3 file path
    'alert_failure' => '{{alert_failure}}',
);
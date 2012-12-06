<?php
return array(
    // array of services to watch
    'services'  => array(
        '{{service_name}}' => "{{service_url}}",
        ),
    // how often do you want to ping services for their status
    'status_check_frequency'  =>  '{{status_check_frequency}}',
    // enable alert types
    'alerts'    => array('email','audio'),
    'alert_email'   =>  array(
        'to'    => '{{alert_email_to}}',
        'subject'   =>  '{{alert_email_subject}}'
    ),
    // how often do you want to play alerts when they are unchanged (status check changes are immediatley sent)
    'alert_frequency'  =>  '{{alert_frequency}}',
    // set successful status alert wav or mp3 file path
    'alert_success' => '{{alert_success}}',
    // set failure status alert wav or mp3 file path
    'alert_failure' => '{{alert_failure}}',
    // set message for when status has changed from failure to success
    'alert_restored' => '{{alert_restored}}',
    // set the desired timezone
    'timezone'  =>  '{{timezone}}',
);
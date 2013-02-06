<?php
require('api_thingy.php');

// Grab our config array
$config = get_config();

$last_run = array(
    // Set our last run success timestamp for 0 so it announces any success results immediatley.
    'success'   =>  0,
    // Set our last run failure timestamp for 0 so it announces any failure results immediatley.
    'failure'   =>  0,
);

// DO ETTTT ALWAYS
while(true)
{
    // We need to have some services configured to do anything... and that should be an array
    if(isset($config['service_clusters']) && is_array($config['service_clusters']))
    {
        // Get any alerts on status --- irregular responses or missing services
        $status_alerts = get_status_alerts();

        // If we have alerts turned on
        if(! empty($config['alerts']))
        {
            // If we didn't get any status alerts
            if (empty($status_alerts))
            {
                // If we have met the announcement threshold, we can make announcements OR we are changing status
                if (announcement_threshold($last_run['success']) || ($last_run['failure'] > $last_run['success']))
                {
                    // Check for audio alerts
                    if (in_array('audio', $config['alerts']))
                    {
                        // Fire audio alerts
                        audio_success($last_run);
                    }

                    // reset our last run timestamp
                    $last_run['success'] = strtotime('now');
                }
            }
            else
            {
                // If we have met the announcement threshold, we can make announcements OR we are changing status
                if (announcement_threshold($last_run['failure']) || ($last_run['success'] > $last_run['failure']))
                {
                    // Check for audio alerts
                    if (in_array('audio', $config['alerts']))
                    {
                        // Fire audio alerts
                        audio_failure($status_alerts);
                    }

                    // Check for email alerts
                    if (in_array('email', $config['alerts']))
                    {
                        // Fire email alerts
                        send_email($status_alerts);
                    }

                    // reset our last run timestamp
                    $last_run['failure'] = strtotime('now');
                }
            }
        }
    }

    // Sleep until next status check
    sleep($config['status_check_frequency']);
}

/**
 * determine if the announcement threshold has been met
 *
 * @param $timestamp
 * @return bool
 */
function announcement_threshold($timestamp)
{
    // Grab our config array
    $config = get_config();

    // return a bool depending on if we've hit our announcement frequency threshold
    return ((strtotime('now') - $config['alert_frequency']) > $timestamp) ? true : false;
}

/**
 * Send alert emails on failure status(es)
 *
 * @param Array $alerts
 */
function send_email($alerts = array())
{
    // Grab our config array
    $config = get_config();

    // Construct a message with each alert on a new line
    $message = implode("\n", $alerts);

    // Grab our to address from the conf
    $to = $config['alert_email']['to'];

    // Grab our subject from the conf and add a date time string to it for unqieness
    $subject = $config['alert_email']['subject'] . " " . get_formatted_datetime();

    $headers = 'From: so-gnar@no-reply' . "\r\n" .
        'Reply-To: so-gnar@no-reply' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    // Send the message with provided details
    mail($to, $subject, $message, $headers);
}

/**
 * Gets formatted datetime of NOW
 *
 * @return string
 */
function get_formatted_datetime()
{
    // Grab our config array
    $config = get_config();

    // Start DateTime, set it to configured DateTimeZone, and format it how I want it.
    $now = new DateTime();
    $now->setTimezone(new DateTimeZone($config['timezone']));
    return $now->format('m/d/y g:i a');
}

/**
 * grab the config as array
 *
 * @return mixed
 */
function get_config()
{
    // Our supposed conf path
    $conf_path = 'conf/conf.php';

    // CONF
    if (! file_exists($conf_path))
    {
        die("You need a conf.php, bro.");
    }

    // load that conf array up and return it!
    return include $conf_path;
}

/**
 * run the audio failure commands
 *
 * @param array $status_alerts
 */
function audio_failure($status_alerts = array())
{
    // Grab our config array
    $config = get_config();

    // We had failures!
    exec('afplay "' . $config['alert_failure'] . '"');
    if(is_array($status_alerts))
    {
        foreach($status_alerts as $status_alert)
        {
            exec('say "' . $status_alert . '"');
        }
    }
}

/**
 * run the audio success commands
 *
 * @param $last_run
 */
function audio_success($last_run)
{
    // Grab our config array
    $config = get_config();

    // We made it without any failures!
    exec('afplay "' . $config['alert_success'] . '"');

    if ($last_run['failure'] > $last_run['success'])
    {
        exec('say "' . $config['alert_restored'] . '"');
    }
}

/**
 * loop through our setup services and sniff for status alerts
 *
 * @return array
 */
function get_status_alerts()
{
    // Grab our config array
    $config = get_config();

    // setup our alerts array
    $alerts = array();

    $api = new API_Thingy();

    // loop thourhg our defined services and check status
    foreach ($config['service_clusters'] as $service_cluster)
    {
        if (isset($service_cluster['credentials']))
        {
            $api->set_credentials($service_cluster['credentials']);
        }

        foreach ($service_cluster['endpoints'] as $endpoint_name => $endpoint)
        {
            $response = $api->status($endpoint);
            switch ($response['curl_info']['http_code'])
            {
                case 200:
                    break;
                case 400:
                    $alerts[] = "{$endpoint_name} thinks you are making a bad request.";
                    break;
                case 401:
                    if (isset($service_cluster['credentials']))
                    {
                        $alerts[] = "{$endpoint_name} thinks you have bad credentials.";
                    }
                    break;
                case 403:
                    $alerts[] = "{$endpoint_name} thinks this is forbidden.";
                    break;
                case 404:
                    $alerts[] = "{$endpoint_name} is not found.";
                    break;
                case 500:
                    $alerts[] = "{$endpoint_name} is broken.";
                    break;
                default:
                    $alerts[] = "{$endpoint_name} is returning an Unknown status code.";
                    break;
            }
        }
    }

    return $alerts;
}
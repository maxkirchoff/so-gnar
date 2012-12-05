<?php
require('api_thingy.php');

// CONF
if (! file_exists('conf/conf.php'))
{
    die("You need a conf.php, bro.");
}
// REQUIRE!
$config = include 'conf/conf.php';

// DO ETTTT ALWAYS
while(true)
{
    if(isset($config['services']) && is_array($config['services']))
    {
        $alerts = array();

        foreach ($config['services'] as $service => $service_url)
        {
            $status = API_Thingy::status($service_url);

            switch ($status)
            {
                case 200:
                    break;
                case 400:
                    // Swallow bad request since we'll get that with non-authed calls
                    break;
                case 401:
                    // Swallow Unauthorized as we'll get that with non-auth calls
                    break;
                case 403:
                    $alerts[] = "{$service} is in the forbidden zone.";
                    break;
                case 404:
                    $alerts[] = "{$service} is not found.";
                    break;
                case 500:
                    $alerts[] = "{$service} is broken.";
                    break;
                default:
                    $alerts[] = "{$service} is acting weird.";
                    break;
            }
        }

        if($config['alerts'])
        {
            if (empty($alerts))
            {
                // We made it without any failures!
                exec('afplay "' . $config['alert_success'] . '"');
            }
            else
            {
                // We had failures!
                exec('afplay "' . $config['alert_failure'] . '"');
                if(is_array($alerts))
                {
                    foreach($alerts as $alert)
                    {
                        exec('say "' . $alert . '"');
                    }
                }
            }
        }
    }

    sleep($config['frequency']);
}
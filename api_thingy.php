<?php
// cURL API wrapper class
class API_Thingy
{
    protected $credentials = array();

    /**
     * @param $credentials
     */
    function set_credentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @param string $endpoint
     * @return array|null
     */
    function get($endpoint = '')
    {
        return $this->api("GET", $endpoint);
    }

    /**
     * @param string $endpoint
     * @return array|null
     */
    function status($endpoint = '')
    {
        return $this->api("GET", $endpoint);
    }

    /**
     * Make an api request
     *
     * @param string $method
     * @param $endpoint
     * @param array $payload
     * @return array|null
     */
    function api($method = 'GET', $endpoint, $payload = array())
    {
        try
        {
            $process = curl_init($endpoint);
            curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            if (isset($this->credentials['username']) && isset($this->credentials['password']))
            {
                curl_setopt($process, CURLOPT_USERPWD, $this->credentials['username'] . ":" . $this->credentials['password']);
            }
            curl_setopt($process, CURLOPT_TIMEOUT, 15);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

            // Check for special handling by method
            switch($method)
            {
                case "POST":
                    curl_setopt($process, CURLOPT_POST, 1);
                    break;
                case "PATCH":
                    curl_setopt($process, CURLOPT_CUSTOMREQUEST, $method);
                    break;
                case "PUT":
                    curl_setopt($process, CURLOPT_CUSTOMREQUEST, $method);
                    break;
                case "GET":
                default:
                    break;
            }

            // If we have a payload, shove it in
            if (! empty($payload))
            {
                $encoded_payload = json_encode($payload);
                curl_setopt($process, CURLOPT_POSTFIELDS, $encoded_payload);
            }

            // Grab the response
            $response_payload = curl_exec($process);

            // Grab the curl info
            $curl_info = curl_getinfo($process);

            // Close up the curl process
            curl_close($process);
        }
        catch (Exception $e)
        {
            return null;
        }

        // Return the response and the curl info
        return array(
            'payload'  => json_decode($response_payload, true),
            'curl_info' =>  $curl_info
        );
    }
}
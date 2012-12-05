<?php
// cURL API wrapper class
class API_Thingy
{
    /**
     * @param string $service_url
     * @return mixed|null
     */
    public static function status($service_url)
    {
        // construct our api full url
        $api_url = "http://{$service_url}";

        $process = curl_init($api_url);
        curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($process, CURLOPT_TIMEOUT, 60);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($process);
        $curl_info = curl_getinfo($process);
        curl_close($process);

        return $curl_info['http_code'];
    }
}
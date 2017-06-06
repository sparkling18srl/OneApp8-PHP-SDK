<?php

namespace OneApp8\HttpClient;

/**
 * Class CurlClient
 */
class CurlClient
{
    const DEFAULT_TIMEOUT = 60;

    const DEFAULT_CONNECTION_TIMEOUT = 30;

    private static $instance = null;

    /**
     * To get the singleton instance
     *
     * @return CurlClient instance object
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * To execute the HTTP request
     *
     * @param string method, The http method
     * @param string baseUrl, The base url to call
     * @param array heades, The map of request's headers
     * @param array params optional, The map of query params to pass in URL
     * @param string body optional, The http body request
     * @return void
     */
    public function request($method, $baseUrl, $headers, $params = null, $body = null)
    {
        $ch = curl_init();
        $requestHeaders = array();
        if (count($headers)) {
            $requestHeaders = [];
            foreach ($headers as $key => $value) {
                $requestHeaders[] = $key . ':' . $value;
            }
            $requestHeaders[] = 'Content-Type: application/json';
        }
        $url = $baseUrl;
        $opts = array();
        switch (strtoupper($method)) {
            case 'GET':
                if (count($params) > 0) {
                    $encoded = self::encode($params);
                    $url = $baseUrl . '?' . $encoded;
                }
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, 1);
                $opts[CURLOPT_POSTFIELDS] = $body;
                break;
            case 'PUT':
                /* curl_setopt($ch, CURLOPT_PUT, 1); */
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                $opts[CURLOPT_POSTFIELDS] = $body;
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default:
                throw new \Exception('HTTP method ' . $method . ' not allowed.');
                break;
        }

        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = self::DEFAULT_CONNECTION_TIMEOUT;
        $opts[CURLOPT_TIMECONDITION] = self::DEFAULT_TIMEOUT;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;
        $opts[CURLOPT_HEADER] = 1;
        $opts[CURLOPT_HTTPHEADER] = $requestHeaders;
        $opts[CURLOPT_VERBOSE] = 1;
        $opts[CURLINFO_HEADER_OUT] = true;

        curl_setopt_array($ch, $opts);

        $response = curl_exec($ch);
        if ($response === false) {
            $errno = curl_errno($ch);
            $message = curl_errno($ch);
            $this->handleError($url, $errno, $message);
        } else {
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $result['header'] = substr($response, 0, $headerSize);
            $result['body'] = substr($response, $headerSize);
        }
        curl_close($ch);

        return $result;
    }

    /**
     * To manage error cases
     *
     * @param string $url
     * @param number $errno
     * @param string $message
     * @return void
     * @throws \Exception
     */
    private function handleError($url, $errno, $message)
    {
        switch ($errno) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $msg = "Could not connet to OneApp8 ($url). Please check your intenet connection "
                    . "and try again. If this problem persists, you shoud check OneApp8's service";
                break;
            default:
                $msg = 'Unexpected error communicating with OneApp8. If this problem persists, ';
        }
        $msg .= ' let us know at support@sparkling18.com';
        $msg .= "\n\n(Network error [errno $errno]: $message)";
        throw new \Exception($msg);
    }


    /**
     * To encode get params
     *
     * @param array $arr A map of param keys to values
     * @return string A querystring
     */
    private static function encode($arr)
    {
        if (!is_array($arr)) {
            return $arr;
        }
        $query = array();
        foreach ($arr as $key => $value) {
            if (!is_array($value)) {
                $query[] = urlencode($k) . '=' . urlencode($value);
            } else {
                $enc = self::encode($value);
                if ($enc) {
                    $query[] = $enc;
                }
            }
        }

        return implode('&', $query);
    }
}

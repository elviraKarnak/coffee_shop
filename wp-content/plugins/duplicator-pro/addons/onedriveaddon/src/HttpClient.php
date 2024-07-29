<?php

namespace Duplicator\Addons\OneDriveAddon;

class HttpClient
{
    /**
     * @var array<string, string> Default headers
     */
    protected $headers = [];

    /**
     * @var string Base URL
     */
    protected $baseUrl = '';

    /**
     * @var int Timeout in seconds
     */
    protected $timeout = 0;

    /**
     * Set the timeout for http calls
     *
     * @param int|float $timeout Timeout in seconds
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        if ($timeout > 0) {
            $this->timeout = $timeout;
        } else {
            $this->timeout = 0;
        }

        return $this;
    }

    /**
     * @param string                $url     The URL to request
     * @param array<string, scalar> $data    The data to send
     * @param array<string, string> $headers The headers to send
     *
     * @return array{headers: array<string, string>, body: string, code: int}
     * @throws \Exception
     */
    public function get($url, $data = [], $headers = [])
    {
        return $this->request('GET', $url, $data, $headers);
    }

    /**
     * @param string                      $url     The URL to request
     * @param array<string, mixed>|string $data    The data to send
     * @param array<string, string>       $headers The headers to send
     *
     * @return array{headers: array<string, string>, body: string, code: int}
     */
    public function post($url, $data = [], $headers = [])
    {
        return $this->request('POST', $url, $data, $headers);
    }

    /**
     * @param string                      $url     The URL to request
     * @param array<string, mixed>|string $data    The data to send
     * @param array<string, string>       $headers The headers to send
     *
     * @return array{headers: array<string, string>, body: string, code: int}
     */
    public function put($url, $data = [], $headers = [])
    {
        return $this->request('PUT', $url, $data, $headers);
    }

    /**
     * @param string                $url     The URL to request
     * @param array<string, string> $headers The headers to send
     *
     * @return array{headers: array<string, string>, body: string, code: int}
     */
    public function delete($url, $headers = [])
    {
        return $this->request('DELETE', $url, [], $headers);
    }

    /**
     * @param string $token Bearer token
     *
     * @return $this
     */
    public function setBearerToken($token)
    {
        $this->headers['Authorization'] = 'Bearer ' . $token;

        return $this;
    }

    /**
     * @param string $url Base URL
     *
     * @return $this
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;

        return $this;
    }

    /**
     * Send a HTTP request
     *
     * @param string                      $method          The request verb
     * @param string                      $url             The URL to request
     * @param array<string, mixed>|string $data            The data to send, arrays will be json encoded, strings will be sent as is
     * @param array<string, string>       $headers         The headers to send
     * @param array<int, scalar>          $overrideOptions Override the curl options
     *
     * @return array{headers: array<string, string>, body: string, code: int}
     */
    public function request($method, $url, $data = [], $headers = [], $overrideOptions = [])
    {
        $headers = array_merge($this->headers, $headers);
        if (!empty($this->baseUrl) && strpos($url, 'http') !== 0) {
            $url = rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
        }

        $curl = curl_init();

        if (! $curl) {
            throw new \Exception('Could not initialize remote request using curl.');
        }

        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
            $data = [];
        }

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HEADER         => true,
        ];

        // We are sending a json payload
        if (!empty($data) && is_array($data)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
            $headers['Content-Type']     = 'application/json';
        } elseif (!empty($data) && is_string($data)) {
            // We are sending a string payload
            $options[CURLOPT_POST]       = true;
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        // Format the headers for curl and set the option
        $options[CURLOPT_HTTPHEADER] = $this->formatRequestHeaders($headers);

        // Override the options if needed
        if (!empty($overrideOptions)) {
            $options = array_merge($options, $overrideOptions);
        }

        // Set the options
        if (! curl_setopt_array($curl, $options)) {
            // curl will return immediately if it fails to set one of the options
            throw new \Exception('Could not set curl options.');
        }

        $response = curl_exec($curl);

        // check for any error
        $error = curl_error($curl);

        if ($error || $response === false) {
            throw new \Exception("Curl error: {$error}");
        }

        // Get the header size and the http code
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $httpCode   = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Get the header and the body
        $header = substr($response, 0, $headerSize);
        $body   = substr($response, $headerSize);

        curl_close($curl);

        return [
            'headers' => $this->formatResponseHeaders($header),
            'body'    => $body,
            'code'    => (int) $httpCode,
        ];
    }

    /**
     * Format the headers for curl
     *
     * @param array<string, string> $headers The headers to format
     *
     * @return string[]
     */
    protected function formatRequestHeaders($headers)
    {
        return array_map(function ($key, $value) {
            return $key . ': ' . $value;
        }, array_keys($headers), $headers);
    }

    /**
     * Format the response headers
     *
     * @param string $headers The headers to format
     *
     * @return false|string[]
     */
    protected function formatResponseHeaders($headers)
    {
        $headers = explode("\r\n", $headers);
        foreach ($headers as $index => $item) {
            $item = explode(': ', $item);
            if (count($item) === 2) {
                $headers[trim($item[0])] = trim($item[1]);
            }
            unset($headers[$index]);
        }

        return $headers;
    }
}

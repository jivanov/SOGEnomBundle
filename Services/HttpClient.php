<?php

/*
 * This file is part of the SOG/EnomBundle
 *
 * (c) Shane O'Grady <shane.ogrady@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SOG\EnomBundle\Services;

use SOG\EnomBundle\Services\EnomException;

/**
 * cURL client
 *
 * @author Shane O'Grady <shane.ogrady@gmail.com>
 */
class HttpClient
{
    protected $url;
    protected $username;
    protected $password;

    protected $payload = array();

    /**
     * Initializes Http client
     *
     * @param string $url      Enom reseller URL
     * @param string $username Enom Account login ID
     * @param string $password Enom Account password
     */
    public function __construct($url, $username, $password)
    {
        $scheme = parse_url($url);
        if (!isset($scheme['path']) || !$scheme['path']) {
            $url .= '/interface.asp';
        }
        
        $this->url      = $url;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Send API request to Enom
     *
     * @param string $command Enom API command/method
     * @param string $payload Request information
     *
     * @return SimpleXMLElement
     */
    protected function makeRequest($command, $payload)
    {
        $payload['command'] = $command;
        $payload['uid']     = $this->username;
        $payload['pw']      = $this->password;
        // We want to return XML and not plain text
        // A JSON response is not yet implemented by Enom
        $payload['responsetype']      = "XML";

        $url = $this->url . '?' . http_build_query($payload);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "SOGEnomBundle");
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        $result = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($result);

        if ((isset($xml->ErrCount)) && ((int) $xml->ErrCount > 0)) {
            throw new EnomException($xml->errors->Err1);
        }

        return $xml;
    }

}

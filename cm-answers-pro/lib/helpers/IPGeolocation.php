<?php

class CMA_IPGeolocation {
	
	const TIMEOUT = 5; // sec
	const FORMAT = 'json';
	
    protected $errors = array();
    protected $service = 'api.ipinfodb.com';
    protected $version = 'v3';
    protected $apiKey = '';

    public function setKey($key) {
        if( !empty($key) ) {
            $this->apiKey = $key;
        }
    }

    public function getError() {
        return implode("\n", $this->errors);
    }

    public function getCountry($host, $isHostName = false) {
        return $this->getResult($host, 'ip-country', $isHostName);
    }

    public function getCity($host, $isHostName = false) {
        return $this->getResult($host, 'ip-city', $isHostName);
    }
	
    private function getResult($host, $name, $isHostName = false) {
        if ($isHostName) $ip = @gethostbyname($host);
        else $ip = $host;
        $url = sprintf('http://%s/%s/%s/?key=%s&ip=%s&format=%s',
        		$this->service,
        		$this->version,
        		urlencode($name),
        		urlencode($this->apiKey),
        		urlencode($ip),
        		self::FORMAT
        );
        $result = @file_get_contents($url, false, stream_context_create(array('http' => array('timeout' => self::TIMEOUT))));
        return json_decode($result, true);
    }

}

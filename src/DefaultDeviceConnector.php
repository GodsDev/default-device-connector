<?php

namespace GodsDev\DefaultDeviceConnector;

//adapt backyard_getJsonAsArray defined in https://github.com/GodsDev/backyard/blob/master/src/backyard_json.php to proper classes
/**
 * Clean comments of json content and decode it with json_decode(). 
 * Work like the original php json_decode() function with the same params 
 * http://www.php.net/manual/en/function.json-decode.php#112735
 * 
 * @param   string  $json2decode    The json string being decoded 
 * @param   bool    $assoc   When TRUE, returned objects will be converted into associative arrays. 
 * @param   integer $depth   User specified recursion depth. (>=5.3) 
 * @param   integer $options Bitmask of JSON decode options. (>=5.4) 
 * @return  array or NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit. 
 */
function ddc_backyard_jsonCleanDecode($json2decode, $assoc = false, $depth = 512, $options = 0) {
    // search and remove comments like /* */ and //
    $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json2decode);
    if (version_compare(phpversion(), '5.4.0', '>=')) {
        $json = json_decode($json, $assoc, $depth, $options);
    } elseif (version_compare(phpversion(), '5.3.0', '>=')) {
        $json = json_decode($json, $assoc, $depth);
    } else {
        $json = json_decode($json, $assoc);
    }
    if (is_null($json)) {
        error_log("Invalid JSON: " . $json2decode, 5);
        return false; //invalid JSON
    }
    return $json;
}

/**
 * @desc Retrieves JSON from $url and puts it into associative array
 * @param string $url
 * @return array|bool array if cURL($url) returns JSON else false
 */
function ddc_backyard_getJsonAsArray($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $json = curl_exec($ch);
    if (!$json) {
        error_log("Curl error: " . curl_error($ch) . " on {$url}");
        return false;
    }
    curl_close($ch);
    $jsonArray = ddc_backyard_jsonCleanDecode($json, true);
    if (!$jsonArray) {
        //error_log("Trouble with decoding JSON from {$url}");
        return false;
    }
    return $jsonArray;
}

class DefaultDeviceConnector {

    protected $apiUrl = '';
    protected $characteristics = array();
    public $properties = array();
    public $error = NULL;

    /**
     * 
     * @param string $defaultDeviceApiUrl
     */
    public function __construct(//string 
    $defaultDeviceApiUrl = 'http://m.t-mobile.cz/services/defaultdevice/api/v2/request/' //latest API URL            
    ) {
        $this->apiUrl = $defaultDeviceApiUrl;
        $this->defaultCharacteristics(); //current HTTP headers are default
    }

    /**
     * Sets client characteristics according to current HTTP headers
     * @return array
     */
    public function defaultCharacteristics() {
        $characteristics = array(//set the array from the scratch
            'user_agent' => (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ""),
        );
        if (isset($_SERVER["HTTP_X_WAP_PROFILE"])) {
            $characteristics["x-wap-profile"] = $_SERVER["HTTP_X_WAP_PROFILE"];
        }
        if (isset($_SERVER["HTTP_ACCEPT"])) {
            $characteristics["accept"] = $_SERVER["HTTP_ACCEPT"];
        }

        //$h = apache_request_headers();
        // Opera mini
        //if (isset($h['X-OperaMini-Phone-UA'])) {
        if (isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])) {
            $characteristics["x-operamini-phone-ua"] = $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'];
        }
        // https://dev.opera.com/blog/introducing-device-stock-ua/
        if (isset($_SERVER['HTTP_DEVICE_STOCK_UA'])) {
            $characteristics['device-stock-ua'] = $_SERVER['HTTP_DEVICE_STOCK_UA'];
        }
        return $this->setCharacteristics($characteristics);
    }

    /**
     * to be accessed by PHPUnit tests or to get User-agent
     * Call example: ->getCharacteristics('user_agent');
     * @return mixed array OR string OR null
     */
    public function getCharacteristics($ch = NULL) {
        if (is_null($ch)) {
            return $this->characteristics;
        } elseif (isset($this->characteristics[$ch])) {
            return (string) $this->characteristics[$ch];
        }
        return NULL;
    }

    /**
     * Sets client characteristics as needed
     * 
     * @param array $characteristics
     * @return mixed array or false
     */
    public function setCharacteristics(array $characteristics) {
        if (!isset($characteristics['user_agent'])) {
            //@todo throw exception
            return false;
        }
        $this->characteristics = array('user_agent' => $characteristics['user_agent'],);
        foreach (array('x-wap-profile', 'accept', 'x-operamini-phone-ua', 'device-stock-ua',) as $v) {
            if (isset($characteristics[$v])) {
                $this->characteristics[$v] = $characteristics[$v];
            }
        }
        return $this->characteristics;
    }

    /**
     * Performs the API call
     * @return mixed array or false
     */
    public function request() {
        if (!$this->characteristics['user_agent'] || empty($this->characteristics['user_agent'])) {
            return $this->setError('user agent missing');
        }

        //this workaround below may lead to proposing Android HTML5 to browser where Android xHTML would be more appropriate
        //@todo enable API to accept x-operamini-phone-ua and then remove this condition
        if (isset($this->characteristics["x-operamini-phone-ua"]) && !empty($this->characteristics["x-operamini-phone-ua"])) {
            $this->characteristics['user_agent'] = $this->characteristics["x-operamini-phone-ua"];
            unset($this->characteristics["x-operamini-phone-ua"]);
        }
        //@todo enable API to accept x-operamini-phone-ua and then remove this condition
        if (isset($this->characteristics['device-stock-ua']) && !empty($this->characteristics['device-stock-ua'])) {
            $this->characteristics['user_agent'] = $this->characteristics['device-stock-ua'];
            unset($this->characteristics['device-stock-ua']);
        }

        //backyard_getJsonAsArray defined in https://github.com/GodsDev/backyard/blob/master/src/backyard_json.php
        //@todo POST instead of GET        
        $resultArray = ddc_backyard_getJsonAsArray($this->apiUrl . "?" . http_build_query($this->characteristics));
        if (!$resultArray) {
            return $this->setError('not json');
        }
        if (isset($resultArray['error'])) {
            return $this->setError($resultArray['error']);
        }
        $this->error = NULL;
        return $this->properties = $resultArray;
    }

    /**
     * If called without previous request() call, then request() call is initiated
     * @return string xhtml, desktop, html5 (default even if API call fails)
     */
    public function getMarkup() {
        if (!empty($this->properties)) {
            if (in_array($this->properties["preferred_template"], array('wml_bw', 'wml_color', 'xhtml',))) {
                return 'xhtml';
            }

            if (in_array($this->properties["device_class"], array("desktop", "bot", "other", 'desktop_tv', 'gamesconsole',))) {
                return 'desktop';
            }

            return 'html5';
        } elseif (!is_null($this->error)) {
            return 'html5'; //default if API call fails
        }
        $this->request();
        return $this->getMarkup();
    }

    /**
     * Usage:
     *  getProperty('operating_system_name')
     *  getProperty('browser')
     *  getProperty('vendor')
     * 
     * If called without previous request() call, then request() call is initiated
     * If the value is unavailable, 'unknown' is returned.
     * 
     * @param string $propertyName
     * @return string
     */
    public function getProperty($propertyName) {
        if (!empty($this->properties)) {
            if (isset($this->properties[$propertyName])) {
                return $this->properties[$propertyName];
            }
            return 'unknown';
        } elseif (!is_null($this->error)) {
            return 'unknown';
        }
        $this->request();
        return $this->getProperty($propertyName);
    }
    
    /**
     * preg_match against all the field that may contain User-agent
     * Returns true with first match
     * 
     * @param array $regexp
     * @return boolean
     */
    public function matchUserAgent(array $regexp) {
        foreach (array('user_agent', 'x-operamini-phone-ua', 'device-stock-ua') as $fieldName) {
            if (isset($this->characteristics[$fieldName])) {
                foreach ($regexp as $v) {
                    if (preg_match("/{$v}/i", $this->characteristics[$fieldName])){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Log error and always return false
     * @param mixed $error string preffered
     * @return false
     */
    private function setError($error) {
        $this->error = $error;
        error_log(date('Y-m-d H:i:s') . ' ' . __FILE__ . ' '
                . "default-device-connector " . (string) $error . " with ch=" . http_build_query($this->characteristics) . PHP_EOL, 3, __DIR__ . '/log/error.log.' . date('Y-m-d') . '.log');
        return false;
    }

}

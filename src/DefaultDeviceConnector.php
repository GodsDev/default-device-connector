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

class DefaultDeviceConnector
{
    protected $apiUrl = '';
    protected $characteristics = array();
    public $properties = array();
    
    public function __construct(//string 
        $defaultDeviceApiUrl) {
        $this->apiUrl = $defaultDeviceApiUrl;
    }
    
    public function defaultCharacteristics(){
        $this->characteristics['user_agent'] = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "");
        if (isset($_SERVER["HTTP_X_WAP_PROFILE"])) {
            $this->characteristics["x-wap-profile"] = $_SERVER["HTTP_X_WAP_PROFILE"];
        }
        if (isset($_SERVER["HTTP_ACCEPT"])) {
            $this->characteristics["accept"] = $_SERVER["HTTP_ACCEPT"];
        }
    }
    
    public function setCharacteristics(array $characteristics){
        if (!isset($characteristics['user_agent'])){
            //@todo throw exception
            return false;
        }
        $this->characteristics['user_agent'] = $characteristics['user_agent'];
        if (isset($characteristics["x-wap-profile"])) {
            $this->characteristics["x-wap-profile"] = $characteristics["x-wap-profile"];
        }
        if (isset($characteristics["accept"])) {
            $this->characteristics["accept"] = $characteristics["accept"];
        }        
    }
    
    public function request(){        
        //backyard_getJsonAsArray defined in https://github.com/GodsDev/backyard/blob/master/src/backyard_json.php
        //@todo POST instead of GET
        $resultArray = ddc_backyard_getJsonAsArray($this->apiUrl . "?" . http_build_query($this->characteristics));
        if(isset($resultArray['error'])){
            return false;                    
        }
        return $this->properties = $resultArray;
    }
}

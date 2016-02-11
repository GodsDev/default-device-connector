<?php 
require_once __DIR__ . '/../src/DefaultDeviceConnector.php';

$deviceProperties = new GodsDev\DefaultDeviceConnector\DefaultDeviceConnector('http://m.t-mobile.cz/services/defaultdevice/api/v2/request/');
$deviceProperties->defaultCharacteristics();
var_dump($deviceProperties->request());
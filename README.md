# default-device-connector
Connector for defauldevice service

```php
$deviceProperties = new GodsDev\DefaultDeviceConnector\DefaultDeviceConnector('http://m.t-mobile.cz/services/defaultdevice/api/v2/request/');
$deviceProperties->defaultCharacteristics();
var_dump($deviceProperties->request());
```

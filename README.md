# default-device-connector
PHP connector for defauldevice service

For security (not revealing files that SHOULD NOT be accessed through browser)
mod_alias.c SHOULD be enabled in the directory where this library lives.

Usage:
```php
$deviceInfo = new GodsDev\DefaultDeviceConnector\DefaultDeviceConnector(); //may be initiated with non default latest API URL
var_dump($deviceInfo->request());
var_dump($deviceInfo->getMarkup());
```

## Method request()
Returns either array with device properties
(and sets *$deviceInfo->properties* to the same value)
or returns *false* and puts details into *$deviceInfo->error*

Note: workaround before default device API can process it directly: 'Device-Stock-UA' takes precedence before 'X-OperaMini-Phone-UA' takes precedence before 'HTTP_USER_AGENT'.


## Method getMarkup()
Returns one of strings
* *html5* (which is default even in case of API call error)
* *desktop*
* *xhtml*

## Method setCharacteristics(array $characteristics)
Sets arbitrarily the values to request the *default device API* with.

'user_agent' key MUST be present.

'x-wap-profile', 'accept', 'x-operamini-phone-ua', 'device-stock-ua' keys MAY be present.

## Method defaultCharacteristics()
Populates the values to request the *default device API* with according to the current HTTP headers.

Is called automatically by the *__constructor*.

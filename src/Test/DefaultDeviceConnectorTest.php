<?php

namespace GodsDev\DefaultDeviceConnector\Test;

//use GodsDev\DefaultDeviceConnector;

class DefaultDeviceConnectorTest extends \PHPUnit_Framework_TestCase {
//    public function testTrueIsTrue() {
//        $foo = true;
//        $this->assertTrue($foo);
//    }

    /**
     * Does not really test anything by PHPUnit, since $_SERVER array does not contain HTTP_USER_AGENT
     */
    public function testDefaultCharacteristicsSetCurrentHTTPheaders() {
        $currentHTTPheaders = array();
        $currentHTTPheaders['user_agent'] = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "");
        if (isset($_SERVER["HTTP_X_WAP_PROFILE"])) {
            $currentHTTPheaders["x-wap-profile"] = $_SERVER["HTTP_X_WAP_PROFILE"];
        }
        if (isset($_SERVER["HTTP_ACCEPT"])) {
            $currentHTTPheaders["accept"] = $_SERVER["HTTP_ACCEPT"];
        }

        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceInfo->defaultCharacteristics();
        $this->assertEquals($currentHTTPheaders, $deviceInfo->getCharacteristics());
    }

    public function testSetCharacteristicsEquals() {
        $testArray = array(
            'user_agent' => 'a',
            'x-wap-profile' => 'b',
            'accept' => 'c',
            'x-operamini-phone-ua' => 'd',
        );

        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceInfo->setCharacteristics($testArray);
        $this->assertEquals($testArray, $deviceInfo->getCharacteristics());
    }

    public function testSetCharacteristicsNotEquals() {
        $testArray = array(
            'user_agent' => 'a',
            'foo2' => 'g', //SHOULD be ignored
        );

        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceInfo->setCharacteristics($testArray);
        $this->assertNotEquals($testArray, $deviceInfo->getCharacteristics());
    }

    public function testSetCharacteristicsRepeated() {
        $testArray = array(
            'user_agent' => 'a',
            'x-wap-profile' => 'b',
            'accept' => 'c',
        );

        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceInfo->setCharacteristics($testArray);

        $testArray = array(
            'user_agent' => 'f',
        );

        $deviceInfo->setCharacteristics($testArray);
        $this->assertEquals($testArray, $deviceInfo->getCharacteristics());
    }

    /**
     * Always fails with "Failed asserting that 'user agent missing' is null."
     */
//    public function testRequestDefault (){
//        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
//        $result = $deviceInfo->request();
//        $this->assertNull($deviceInfo->error);        
//    }
//    public function testRequestDefaultDeviceConnectorTestNonExistingUserAgent() {
//        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
//        $characteristics = array(
//            'user_agent' => 'DefaultDeviceConnectorTestNonExistingUserAgent',
//                // 'x-wap-profile' => 'b',
//                // 'accept' => 'c',
//        );
//        $deviceInfo->setCharacteristics($characteristics);
//        $result = $deviceInfo->request();
//        var_dump($result);
//        var_dump($deviceInfo->error);
//        $this->assertNotNull($deviceInfo->error);
//        $this->assertEquals('not json', $deviceInfo->error);
//    }

    public function testRequestMissingUserAgent() {
        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $characteristics = array(
            'x-wap-profile' => 'b',
            'accept' => 'c',
        );
        $deviceInfo->setCharacteristics($characteristics);
        $result = $deviceInfo->request();
        $this->assertNotNull($deviceInfo->error);
        $this->assertEquals('user agent missing', $deviceInfo->error);
    }

    public function testRequestAndroid() {
        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $characteristics = array(
            'user_agent' => 'Mozilla/5.0 (Linux; Android 4.2.2; B1-711 Build/JDQ39) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.76 Safari/537.36',
            //'x-wap-profile' => 'b',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        );
        $deviceInfo->setCharacteristics($characteristics);
        $result = $deviceInfo->request();
//        var_dump($result);
        $this->assertNull($deviceInfo->error);
        $this->assertEquals('Android', $result['operating_system_name']);
    }

    public function testRequestWindowsPhone() {
        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $characteristics = array(
            'user_agent' => 'MWP/1.0/Mozilla/5.0 (Mobile; Windows Phone 8.1; Android 4.0; ARM; Trident/7.0; Touch; rv:11.0; IEMobile/11.0; NOKIA; Lumia 925) like iPhone OS 7_0_3 Mac OS X AppleWebKit/537 (KHTML, like Gecko) Mobile Safari/537',
            // 'x-wap-profile' => 'b',
            'accept' => 'text/html, application/xhtml+xml, */*',
        );
        $deviceInfo->setCharacteristics($characteristics);
        $result = $deviceInfo->request();
        $this->assertNull($deviceInfo->error);
        $this->assertEquals('WindowsPhone', $result['operating_system_name']);
    }

    public function testGetPropertyiOS() {
        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $characteristics = array(
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_2 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13C71 Safari/601.1',
            // 'x-wap-profile' => 'b',
            'accept' => 'text/html,application/xhtml xml,application/xml;q=0.9,*/*;q=0.8',
        );
        $deviceInfo->setCharacteristics($characteristics);
//        $result = $deviceInfo->request();
//        $this->assertNull($deviceInfo->error);
//        $this->assertEquals('iOS', $result['operating_system_name']);
        $this->assertEquals('iOS', $deviceInfo->getProperty('operating_system_name'));
        $this->assertEquals('Apple', $deviceInfo->getProperty('vendor'));
    }

    public function testGetMarkupDefaultValue() {
        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $this->assertEquals('html5', $deviceInfo->getMarkup());
    }

    public function testRequestOperaMiniWorkaround() {
        $characteristicsOriginal = array(
            'user_agent' => 'a2',
            'x-wap-profile' => 'b3',
            'accept' => 'c2',
            'x-operamini-phone-ua' => 'e3',
                //'device-stock-ua' => 'f5',
        );
        $characteristicsExpected = array(
            'user_agent' => 'e3',
            'x-wap-profile' => 'b3',
            'accept' => 'c2',
        );
        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceInfo->setCharacteristics($characteristicsOriginal);
        $deviceInfo->request();
        $this->assertEquals($characteristicsExpected, $deviceInfo->getCharacteristics());
    }

    public function testRequestDeviceStockUAWorkaround() {
        $characteristicsOriginal = array(
            'user_agent' => 'a2',
            'x-wap-profile' => 'b3',
            'accept' => 'c2',
            'x-operamini-phone-ua' => 'e3',
            'device-stock-ua' => 'f5',
        );
        $characteristicsExpected = array(
            'user_agent' => 'f5',
            'x-wap-profile' => 'b3',
            'accept' => 'c2',
        );
        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceInfo->setCharacteristics($characteristicsOriginal);
        $deviceInfo->request();
        $this->assertEquals($characteristicsExpected, $deviceInfo->getCharacteristics());
    }

    public function testMatchUserAgent() {
        $characteristics = array(
            'user_agent' => 'a2',
            'x-wap-profile' => 'b3',
            'accept' => 'c2',
            'x-operamini-phone-ua' => 'alfae3',
            'device-stock-ua' => 'f5beta',
        );
        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceInfo->setCharacteristics($characteristics);
        $this->assertTrue($deviceInfo->matchUserAgent(array('a1', 'a2')));
        $this->assertFalse($deviceInfo->matchUserAgent(array('a1', 'a3')));
        $this->assertTrue($deviceInfo->matchUserAgent(array('a1', 'e3', 'a3')));
        $this->assertTrue($deviceInfo->matchUserAgent(array('f5')));
    }

    public function testGetCharacteristicsUserAgent() {
        $characteristics = array(
            'user_agent' => 'a2',
            'x-wap-profile' => 'b3',
            'accept' => 'c2',
            'x-operamini-phone-ua' => 'alfae3',
            'device-stock-ua' => 'f5beta',
        );
        $deviceInfo = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceInfo->setCharacteristics($characteristics);
        $this->assertEquals('a2', $deviceInfo->getCharacteristics('user_agent'));
    }

}

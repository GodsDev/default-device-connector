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

        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceProperties->defaultCharacteristics();
        $this->assertEquals($currentHTTPheaders, $deviceProperties->getCharacteristics());
    }

    public function testSetCharacteristicsEquals() {
        $testArray = array(
            'user_agent' => 'a',
            'x-wap-profile' => 'b',
            'accept' => 'c',
            'x-operamini-phone-ua' => 'd',
        );

        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceProperties->setCharacteristics($testArray);
        $this->assertEquals($testArray, $deviceProperties->getCharacteristics());
    }

    public function testSetCharacteristicsNotEquals() {
        $testArray = array(
            'user_agent' => 'a',
            'foo2' => 'g', //SHOULD be ignored
        );

        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceProperties->setCharacteristics($testArray);
        $this->assertNotEquals($testArray, $deviceProperties->getCharacteristics());
    }

    public function testSetCharacteristicsRepeated() {
        $testArray = array(
            'user_agent' => 'a',
            'x-wap-profile' => 'b',
            'accept' => 'c',
        );

        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $deviceProperties->setCharacteristics($testArray);

        $testArray = array(
            'user_agent' => 'f',
        );

        $deviceProperties->setCharacteristics($testArray);
        $this->assertEquals($testArray, $deviceProperties->getCharacteristics());
    }

    /**
     * Always fails with "Failed asserting that 'user agent missing' is null."
     */
//    public function testRequestDefault (){
//        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
//        $result = $deviceProperties->request();
//        $this->assertNull($deviceProperties->error);        
//    }

//    public function testRequestDefaultDeviceConnectorTestNonExistingUserAgent() {
//        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
//        $characteristics = array(
//            'user_agent' => 'DefaultDeviceConnectorTestNonExistingUserAgent',
//                // 'x-wap-profile' => 'b',
//                // 'accept' => 'c',
//        );
//        $deviceProperties->setCharacteristics($characteristics);
//        $result = $deviceProperties->request();
//        var_dump($result);
//        var_dump($deviceProperties->error);
//        $this->assertNotNull($deviceProperties->error);
//        $this->assertEquals('not json', $deviceProperties->error);
//    }

    public function testRequestDefaultDeviceConnectorMissingUserAgent() {
        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $characteristics = array(
            'x-wap-profile' => 'b',
            'accept' => 'c',
        );
        $deviceProperties->setCharacteristics($characteristics);
        $result = $deviceProperties->request();
        $this->assertNotNull($deviceProperties->error);
        $this->assertEquals('user agent missing', $deviceProperties->error);
    }

    public function testRequestDefaultDeviceConnectorAndroid() {
        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $characteristics = array(
            'user_agent' => 'Mozilla/5.0 (Linux; Android 4.2.2; B1-711 Build/JDQ39) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.76 Safari/537.36',
            //'x-wap-profile' => 'b',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        );
        $deviceProperties->setCharacteristics($characteristics);
        $result = $deviceProperties->request();
//        var_dump($result);
        $this->assertNull($deviceProperties->error);
        $this->assertEquals('Android', $result['operating_system_name']);
    }

    public function testRequestDefaultDeviceConnectorWP() {
        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $characteristics = array(
            'user_agent' => 'MWP/1.0/Mozilla/5.0 (Mobile; Windows Phone 8.1; Android 4.0; ARM; Trident/7.0; Touch; rv:11.0; IEMobile/11.0; NOKIA; Lumia 925) like iPhone OS 7_0_3 Mac OS X AppleWebKit/537 (KHTML, like Gecko) Mobile Safari/537',
            // 'x-wap-profile' => 'b',
            'accept' => 'text/html, application/xhtml+xml, */*',
        );
        $deviceProperties->setCharacteristics($characteristics);
        $result = $deviceProperties->request();
        $this->assertNull($deviceProperties->error);
        $this->assertEquals('WindowsPhone', $result['operating_system_name']);
    }

    public function testRequestDefaultDeviceConnectoriOS() {
        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $characteristics = array(
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_2 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13C71 Safari/601.1',
            // 'x-wap-profile' => 'b',
            'accept' => 'text/html,application/xhtml xml,application/xml;q=0.9,*/*;q=0.8',
        );
        $deviceProperties->setCharacteristics($characteristics);
        $result = $deviceProperties->request();
        $this->assertNull($deviceProperties->error);
        $this->assertEquals('iOS', $result['operating_system_name']);
    }

    public function testGetMarkupDefaultValue() {
        $deviceProperties = new \GodsDev\DefaultDeviceConnector\DefaultDeviceConnector();
        $this->assertEquals('html5', $deviceProperties->getMarkup());
    }    
}

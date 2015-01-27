<?php
require dirname(__DIR__)."/linkChecker.class.php";
class phpUnit extends PHPUnit_Framework_TestCase 
{
    private $links = array();

    public function __construct(){
        $this->links[] = array(
                    "url"            => "https://1fichier.com/?4jf62b2mr4",
                    "is_valid"       => false,
                    "getFormatSize"    => "0Gb",
                    "is_unknow_host" => false,
                    "getHost"           => "1fichier",
                ); 
        $this->links[] = array(
                    "url"            => "https://pzjcjv2sq9.1fichier.com/",
                    "is_valid"       => true,
                    "getFormatSize"    => "1.42Gb",
                    "is_unknow_host" => false,
                    "getHost"           => "1fichier",
                );
        $this->links[] = array(
                    "url"            => "https://1fichier.com/dir/CiLKCBI6",
                    "is_valid"       => false,
                    "getFormatSize"    => "0Gb",
                    "is_unknow_host" => false,
                    "getHost"           => "1fichier",
                );
        $this->links[] = array(
                    "url"            => "https://1fichier.com/dir/CiLKCBI7",
                    "is_valid"       => true,
                    "getFormatSize"    => "431Kb",
                    "is_unknow_host" => false,
                    "getHost"           => "1fichier",
                );
        $this->links[] = array(
                    "url"            => "http://www.multiup.org/fr/download/a8ee679c6c86342eeacac0c828c2344f/Cal6loDAdv1ancedWar9fareUpd1-DarkZero.rar",
                    "is_valid"       => true,
                    "getFormatSize"    => "132.76Mb",
                    "is_unknow_host" => false,
                    "getHost"           => "multiup",
                );
        $this->links[] = array(
                    "url"            => "http://www.multiup.org/fr/download/3be2d8b9a2a7ad491e556821075e0944/bzh2696.rar",
                    "is_valid"       => false,
                    "getFormatSize"    => "0Gb",
                    "is_unknow_host" => false,
                    "getHost"           => "multiup",
                );
        $this->links[] = array(
                    "url"            => "https://depositfiles.com/files/iwuf30bkz",
                    "is_valid"       => true,
                    "getFormatSize"    => "1.22Gb",
                    "is_unknow_host" => false,
                    "getHost"           => "depositfiles",
                );
        $this->links[] = array(
                    "url"            => "http://dfiles.eu/files/fsusoerxm",
                    "is_valid"       => false,
                    "getFormatSize"    => "0Gb",
                    "is_unknow_host" => false,
                    "getHost"           => "depositfiles",
                );
    } 

    public function test_is_valid()
    {
        foreach($this->links as $value){
            $linkChecker = new linkChecker($value["url"]);
            $this->assertEquals($linkChecker->is_valid(), $value["is_valid"]);
        }
    }
    public function test_getFormatSize()
    {
        foreach($this->links as $value){
            $linkChecker = new linkChecker($value["url"]);
            $this->assertEquals($linkChecker->getFormatSize(), $value["getFormatSize"]);
        }
    }
    public function test_is_unknow_host()
    {
        foreach($this->links as $value){
            $linkChecker = new linkChecker($value["url"]);
            $this->assertEquals($linkChecker->is_unknow_host(), $value["is_unknow_host"]);
        }
    }
    public function test_getHost()
    {
        foreach($this->links as $value){
            $linkChecker = new linkChecker($value["url"]);
            $this->assertEquals($linkChecker->getHost(), $value["getHost"]);
        }
    }
}
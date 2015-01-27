<?php
require dirname(__DIR__)."/linkChecker.class.php";
class phpUnit extends PHPUnit_Framework_TestCase 
{
    public function testLink1()
    {
        $linkChecker = new linkChecker("https://1fichier.com/?4jf62b2mr4");
        $this->assertEquals($linkChecker->is_valid(), false);
    }

    public function testLink2()
    {
        $linkChecker = new linkChecker("https://pzjcjv2sq9.1fichier.com/");
        $this->assertEquals($linkChecker->is_valid(), true);
    }

    public function testLink3()
    {
        $linkChecker = new linkChecker("https://1fichier.com/dir/CiLKCBI6");
        $this->assertEquals($linkChecker->is_valid(), false);
    }

    public function testLink4()
    {
        $linkChecker = new linkChecker("https://1fichier.com/dir/CiLKCBI7");
        $this->assertEquals($linkChecker->is_valid(), true);
    }

    public function testLink5()
    {
        $linkChecker = new linkChecker("http://www.multiup.org/fr/download/a8ee679c6c86342eeacac0c828c2344f/Cal6loDAdv1ancedWar9fareUpd1-DarkZero.rar");
        $this->assertEquals($linkChecker->is_valid(), true);
    }

    public function testLink6()
    {
        $linkChecker = new linkChecker("http://www.multiup.org/fr/download/3be2d8b9a2a7ad491e556821075e0944/bzh2696.rar");
        $this->assertEquals($linkChecker->is_valid(), false);
    }

    public function testLink7()
    {
        $linkChecker = new linkChecker("https://depositfiles.com/files/iwuf30bkz");
        $this->assertEquals($linkChecker->is_valid(), true);
    }

    public function testLink8()
    {
        $linkChecker = new linkChecker("http://dfiles.eu/files/fsusoerxm");
        $this->assertEquals($linkChecker->is_valid(), false);
    }

    public function testFormatSizeValide()
    {
        $linkChecker = new linkChecker("https://pzjcjv2sq9.1fichier.com/");
        $this->assertEquals($linkChecker->getFormatSize(), "1.42Gb");
    }

    public function testFormatSizeInvalide()
    {
        $linkChecker = new linkChecker("https://1fichier.com/?4jf62b2mr4");
        $this->assertEquals($linkChecker->getFormatSize(), "0Gb");
    }

}
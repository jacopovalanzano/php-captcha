<?php

require_once realpath( __DIR__ . "/../src/Captcha.php");

use PHPUnit\Framework\TestCase;

class CaptchaTest extends TestCase
{
    private $captcha;

    public function setUp() {
        // Generate a random 5-characters string.
        $randomString = substr(md5(rand()), 0, 5);

        // Create new captcha
        $this->captcha = new Captcha($randomString);
    }

    public function tearDown() {
        // Destroy captcha instance
        $this->captcha = null;
    }

    public function testGetCaptcha()
    {
        $randomString = substr(md5(rand()), 0, 5);

        // Create new captcha
        $captcha = new Captcha($randomString);

        // Generate captcha image
        $captcha->build();

        // Get captcha output
        ob_start();
        $captcha->getCaptcha(100);
        $img = ob_get_clean();

        // Inspect captcha output
        $img = explode("quality = 100", substr($img,0 , 100)); // or 81

        $this->assertSame(2, count($img));
    }

    public function testGetPassphrase()
    {
        // Generate a random string.
        $randomString = substr(md5(rand()), 0, 5);

        // Create new captcha
        $captcha = new Captcha($randomString);

        $this->assertSame($randomString, $captcha->getPassphrase());
    }

    public function testAddFonts()
    {
        $fontsDir = \realpath(__DIR__ . "/../src/Fonts");

        $this->captcha->addFonts([
            $fontsDir . "/Captcha0.ttf",
            $fontsDir . "/Captcha1.ttf",
            $fontsDir . "/Captcha2.ttf",
            $fontsDir . "/Captcha3.ttf",
            $fontsDir . "/Captcha4.ttf"
        ]);

        $this->assertSame(10, count($this->captcha->getFonts()));
    }

    public function testCreateStringImage()
    {
        $randomString = substr(md5(rand()), 0, 5);

        // Create new captcha
        $captcha = new Captcha($randomString);

        // Generate captcha image
        $captcha->createStringImage();

        // Get captcha output
        ob_start();
        $captcha->getCaptcha(100);
        $img = ob_get_clean();

        // Inspect captcha output
        $img = explode("quality = 100", substr($img,0 , 100)); // or 81

        $this->assertSame(2, count($img));
    }

}

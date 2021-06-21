<?php

/**
 * A simple and portable captcha.
 *
 * @author Jacopo Valanzano <jacopo.valanzano@gmail.com>
 *
 * @copyright MIT anonymous
 */
class Captcha
{

    /**
     * The current captcha image instance.
     */
    private $captcha;

    /**
     * The captcha passphrase.
     *
     * @var string
     */
    private $passphrase;

    /**
     * The ttf fonts list.
     *
     * @var array
     */
    private $fonts;

    /**
     * Quantity of lines to display in front of the text layer.
     *
     * @var int
     */
    private $linesFront;

    /**
     * Quantity of lines to display behind the text layer.
     *
     * @var int
     */
    private $linesBack;

    /**
     * Captcha constructor.
     *
     * @param $passphrase
     */
    public function __construct($passphrase)
    {
        $this->passphrase = is_array($passphrase) ? implode(" ", $passphrase) : $passphrase;

        $path = realpath(dirname(__FILE__));

        $this->fonts = [
            "${path}/Fonts/captcha0.ttf",
            "${path}/Fonts/captcha1.ttf",
            "${path}/Fonts/captcha2.ttf",
            "${path}/Fonts/captcha3.ttf",
            "${path}/Fonts/captcha4.ttf"
        ];
    }

    /**
     * Adds new ttf fonts files to the list.
     *
     * @param array $fonts
     */
    public function addFonts($fonts)
    {
        $this->fonts = array_merge($this->fonts, $fonts);
    }

    /**
     * Creates a new captcha.
     *
     * @param int $imgWidth
     * @param int $imgHeight
     * @param int $fontSize
     */
    public function build($imgWidth = 175, $imgHeight = 50, $fontSize = 18)
    {
        $this->createImage($imgWidth, $imgHeight, $fontSize);
    }

    /**
     * Outputs a created captcha, including headers.
     */
    public function out()
    {
        $this->getCaptcha();
    }

    /**
     * Creates a new captcha.
     *
     * @param int $imgWidth
     * @param int $imgHeight
     * @param int $fontSize
     */
    public function createImage($imgWidth = 175, $imgHeight = 50, $fontSize = 18)
    {
        // Initialize an image
        $this->captcha = imagecreatetruecolor($imgWidth, $imgHeight);

        // Select a random color and create a rectangle
        $rcolor = imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255));
        $rectangle = imagefilledrectangle($this->captcha, 0, 0, $imgWidth - 1, $imgHeight - 1, $rcolor);

        // Select one more random color for our letters
        $rcolor = imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255));

        // Create the text bounding (distances to fit container)
        $textBox = imagettfbbox($fontSize, $rectangle, $this->fonts[rand(0, count($this->fonts)-1)], $this->passphrase);

        $textWidth = abs(max($textBox[2], $textBox[4]));

        $textHeight = abs(max($textBox[5], $textBox[7]));

        $x = (imagesx($this->captcha) - $textWidth) / 2;
        $y = (imagesy($this->captcha) + $textHeight) / 2;

        // Creates lines behind the string.
        for($i=0;$i<$this->linesBack;$i++) {
            imageline($this->captcha, rand(0, $imgWidth), rand(0, $imgHeight), rand(0, $imgWidth), rand(0, $imgHeight), imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255)));
        }

        // Add shadow
        imagettftext($this->captcha, $fontSize, $rectangle, $x+rand(-5,5), $y+rand(-5,5), imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255)), $this->fonts[rand(0,count($this->fonts)-1)], $this->passphrase);

        //add the text
        imagettftext($this->captcha, $fontSize, $rectangle, $x, $y, $rcolor, $this->fonts[rand(0,count($this->fonts)-1)], $this->passphrase);

        // Creates lines that overlap the string.
        for($i=0;$i<$this->linesFront;$i++) {
            imageline($this->captcha, rand(0, $imgWidth), rand(0, $imgHeight), rand(0, $imgWidth), rand(0, $imgHeight), imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255)));
        }

        $this->captcha = $this->distort($this->captcha, $imgWidth, $imgHeight, imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255)));
    }

    /**
     * Creates a captcha with PHP default fonts.
     * You should use Captcha::createImage() when possible.
     *
     * @param int $imgWidth
     * @param int $imgHeight
     */
    public function createStringImage($imgWidth = 175, $imgHeight = 50)
    {

        // Initialize an image
        $this->captcha = imagecreatetruecolor($imgWidth, $imgHeight);

        // Select a random color and create a rectangle
        $rcolor = imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255));
        imagefilledrectangle($this->captcha, 0, 0, $imgWidth - 1, $imgHeight - 1, $rcolor);

        // Creates lines behind the string.
        for($i=0;$i<$this->linesBack;$i++) {
            imageline($this->captcha, rand(0, $imgWidth), rand(0, $imgHeight), rand(0, $imgWidth), rand(0, $imgHeight), imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255)));
        }

        // Calculate x and y coordinates
        $x = rand(0, (imagesx($this->captcha)) - $imgWidth / 2);
        $y = rand(0, (imagesy($this->captcha)) - $imgHeight / 2);

        // Creates the shadow of the string.
        imagestring($this->captcha, 4, $x, $y, $this->passphrase, imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255)));

        // Creates another string ±3px on top of the "shadow".
        imagestring($this->captcha, 5, $x+rand(-1,1), $y+rand(-1,1), $this->passphrase, imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255)));

        // Creates lines that overlap the string.
        for($i=0;$i<$this->linesFront;$i++) {
            imageline($this->captcha, rand(0, $imgWidth), rand(0, $imgHeight), rand(0, $imgWidth), rand(0, $imgHeight), imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255)));
        }

        $this->captcha = $this->distort($this->captcha, $imgWidth, $imgHeight, imagecolorallocate($this->captcha, rand(0,255), rand(0,255), rand(0,255)));
    }

    /**
     * For generating random lines that overlap the text.
     *
     * @param $lines Number of lines to create.
     * @return $this
     */
    public function linesFront($lines)
    {
        $this->linesFront = $lines;
        return $this;
    }

    /**
     * For generating random lines behind the text.
     *
     * @param $lines Number of lines to create.
     * @return $this
     */
    public function linesBack($lines)
    {
        $this->linesBack = $lines;
        return $this;
    }

    /**
     * Returns the captcha passphrase
     *
     * @return string
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }

    /**
     * Renders the captcha image.
     *
     * @param int $quality Captcha image quality.
     */
    public function getCaptcha($quality = 100)
    {
        header('Content-Type: image/jpeg');
        imagejpeg($this->captcha, null, $quality);
        imagedestroy($this->captcha);
    }

    /**
     * Image distortion
     */
    protected function distort($image, $width, $height, $bg)
    {
        $contents = imagecreatetruecolor($width, $height);
        $X          = rand(0, $width);
        $Y          = rand(0, $height);
        $phase      = rand(0, 10);
        $scale      = 1.1 + rand(0, 10000) / 30000;
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $Vx = $x - $X;
                $Vy = $y - $Y;
                $Vn = sqrt($Vx * $Vx + $Vy * $Vy);

                if ($Vn != 0) {
                    $Vn2 = $Vn + 4 * sin($Vn / 30);
                    $nX  = $X + ($Vx * $Vn2 / $Vn);
                    $nY  = $Y + ($Vy * $Vn2 / $Vn);
                } else {
                    $nX = $X;
                    $nY = $Y;
                }
                $nY = $nY + $scale * sin($phase + $nX * 0.2);

                $p = $this->getCol($image, round($nX), round($nY), $bg);

                if ($p == 0) {
                    $p = $bg;
                }

                imagesetpixel($contents, $x, $y, $p);
            }
        }

        return $contents;
    }

    /**
     * @param $image
     * @param $x
     * @param $y
     *
     * @return int
     */
    protected function getCol($image, $x, $y, $background)
    {
        $L = imagesx($image);
        $H = imagesy($image);
        if ($x < 0 || $x >= $L || $y < 0 || $y >= $H) {
            return $background;
        }

        return imagecolorat($image, $x, $y);
    }
}
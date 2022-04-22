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
    public function __construct($passphrase = null)
    {
        if(isset($passphrase)) {
            $this->passphrase = \is_array($passphrase) ? \implode(" ", $passphrase) : $passphrase;
        } else {
            $this->passphrase = \substr(\md5(\rand()), 0, 5);
        }

        $path = \realpath(__DIR__);

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
        $this->fonts = \array_merge($this->fonts, $fonts);
    }

    /**
     * Used for phpUnit tests.
     *
     * @return array|string[]
     */
    public function getFonts()
    {
        return $this->fonts;
    }

    /**
     * Creates a new captcha.
     *
     * @param int $imgWidth
     * @param int $imgHeight
     */
    public function build($imgWidth = 175, $imgHeight = 50)
    {
        $this->createImage($imgWidth, $imgHeight);
    }

    /**
     * Outputs the captcha, including headers.
     */
    public function out()
    {
        \header('Content-Type: image/jpeg');
        $this->getCaptcha();
    }

    /**
     * @return string
     */
    public function inline()
    {
        \header('Content-Type: text/html');
        \ob_start();
        $this->getCaptcha();
        return 'data:image/jpeg;base64,' . \base64_encode(\ob_get_clean());
    }

    /**
     * Renders the captcha image.
     *
     * @param int $quality Captcha image quality.
     */
    public function getCaptcha($quality = 100)
    {
        \imagejpeg($this->captcha, null, $quality);
        \imagedestroy($this->captcha);
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
     * Creates a new captcha.
     *
     * @param int $imgWidth
     * @param int $imgHeight
     */
    public function createImage($imgWidth = 175, $imgHeight = 50)
    {
        // Adjust font size:
        $pwl = \strlen($this->getPassphrase());

        // Calculate font size
        $fontSize = ( \sqrt($imgWidth * $imgWidth - $imgHeight * $imgHeight) / $pwl );

        // Create an image
        $this->captcha = \imagecreatetruecolor($imgWidth, $imgHeight);

        // Select a random color for the background and create a rectangle
        $bgcolor = \imagecolorallocate($this->captcha, \rand(100,255), \rand(100,255), \rand(100,255));

        $textColor = $this->invertColor($bgcolor);

        // random background size
        \imagefilledrectangle($this->captcha, 0, 0, $imgWidth, $imgHeight, $bgcolor);

        // Create the text bounding (distances to fit container)
        $textBox = \imagettfbbox($fontSize, false, $this->fonts[\rand(0, \count($this->fonts)-1)], $this->passphrase);

        $textWidth = \abs(\max($textBox[2], $textBox[4]));

        $textHeight = \abs(\max($textBox[5], $textBox[7]));

        // Create lines behind the text
        for($i=0;$i<$this->linesBack;$i++) {
            $rcolor = [$textColor, \imagecolorallocate($this->captcha, \rand(100,255), \rand(100,255), \rand(100,255))];
            \imagesetthickness($this->captcha, \rand(1, 2));
            \imageline($this->captcha, \rand(0, $imgWidth), \rand(0, $imgHeight), \rand(0, $imgWidth), \rand(0, $imgHeight), $rcolor[\rand(0,1)]);
        }

        // Retrieve space left in rectangle
        $x = ((\imagesx($this->captcha) - $textWidth)  / 2);
        $y = (\imagesy($this->captcha) + $textHeight) / 2;

        // Select random font
        $font = $this->fonts[\rand(0,\count($this->fonts)-1)];

        // Add the text
        \imagettftext($this->captcha, $fontSize, 0, $x, $y, $textColor, $font, $this->passphrase);

        // Create lines that overlap the string
        for($i=0;$i<$this->linesFront;$i++) {
            $rcolor = [$textColor, $textColor, \imagecolorallocate($this->captcha, \rand(100,255), \rand(100,255), \rand(100,255))];
            \imagesetthickness($this->captcha, \rand(1, 2));
            \imageline($this->captcha, \rand(0, $imgWidth), \rand(0, $imgHeight), \rand(0, $imgWidth), \rand(0, $imgHeight), $rcolor[rand(0,2)]);
        }

        // Distort created captcha
        $this->captcha = $this->distort($this->captcha, $imgWidth, $imgHeight, $bgcolor);
    }

    /**
     * Creates a captcha with PHP default fonts.
     * This method does not need a font from a ttf file.
     * Use Captcha::createImage() when possible.
     *
     * @param int $imgWidth
     * @param int $imgHeight
     */
    public function createStringImage($imgWidth = 175, $imgHeight = 50)
    {

        // Initialize an image
        $this->captcha = \imagecreatetruecolor($imgWidth, $imgHeight);

        // Select a random color and create a rectangle
        $bgcolor = \imagecolorallocate($this->captcha, \rand(0,255), \rand(0,255), \rand(0,255));

        $textColor = $this->invertColor($bgcolor);

        \imagefilledrectangle($this->captcha, 0, 0, $imgWidth, $imgHeight, $bgcolor);

        // Creates lines behind the string.
        for($i=0;$i<$this->linesBack;$i++) {
            $rcolor = [$textColor, $textColor, \imagecolorallocate($this->captcha, \rand(100,255), \rand(100,255), \rand(100,255))];
            \imageline($this->captcha, \rand(0, $imgWidth), \rand(0, $imgHeight), \rand(0, $imgWidth), \rand(0, $imgHeight), $rcolor[rand(0,2)]);
        }

        // Calculate x and y coordinates
        $x = \rand(0, (\imagesx($this->captcha)) - $imgWidth / 2);
        $y = \rand(0, (\imagesy($this->captcha)) - $imgHeight / 2);

        // Creates the shadow of the string.
        \imagestring($this->captcha, 5, $x, $y, $this->passphrase, $textColor);

        // Create lines that overlap the string.
        for($i=0;$i<$this->linesFront;$i++) {
            $rcolor = [$textColor, $textColor, \imagecolorallocate($this->captcha, \rand(100,255), \rand(100,255), \rand(100,255))];
            \imageline($this->captcha, \rand(0, $imgWidth), \rand(0, $imgHeight), \rand(0, $imgWidth), \rand(0, $imgHeight), $rcolor[rand(0,2)]);
        }

        // Distort created captcha
        $this->captcha = $this->distort($this->captcha, $imgWidth, $imgHeight, $bgcolor);
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
     * @param $image
     * @param $width
     * @param $height
     * @param $bg
     * @return false|GdImage|resource
     */
    protected function distort($image, $width, $height, $bg)
    {
        $contents = \imagecreatetruecolor($width, $height);

        // Divide by 100 (or so) to smooth out distortion rate
        // Above rand(10+, ...) might cause too much distortion, below 5 might not be enough.
        $phase = $this->rand(8, 5) / 100;

        // random values for x-axis & y-axis distortion, adjust to increase/decrease distortion
        $rndX = $this->rand(9, 6);
        $rndY = $this->rand(8, 5);

        // Iterate through each pixel of the x-axis (width)
        for ($x = 0; $x < $width; $x++) {
            // Iterate through each pixel of the y-axis (height)
            for ($y = 0; $y < $height; $y++) {

                // Distortion
                $nY = $y + ( $rndY + $phase ) * \sin($x * 0.1);
                $nX = $x + ( $rndX + $phase ) * \sin($y * 0.1);

                // Gets the color of the pixel at given coordinate
                $p = $this->getColor($image, $nX, $nY, $bg);

                /**
                 * Places the pixel $p where the x and y axis ($x $y) intersect.
                 * "imagesetpixel draws a pixel at the specified coordinate."
                 */
                \imagesetpixel($contents, $x, $y, $p);
            }
        }

        return $contents;
    }

    /**
     * Returns pixel color of coordinate.
     *
     * @param $image
     * @param $x
     * @param $y
     *
     * @return int
     */
    protected function getColor($image, $x, $y, $background)
    {
        $L = \imagesx($image);
        $H = \imagesy($image);
        if ($x < 0 || $x >= $L || $y < 0 || $y >= $H) {
            return $background;
        }

        return \imagecolorat($image, $x, $y);
    }

    /**
     * Invert color hex.
     *
     * @param $hex
     * @return string
     */
    protected function invertColor($hex){

        $ihex = \dechex($hex);

        $r = \dechex(255 - \round(\hexdec(\substr($ihex, 0,2))));
        $g = \dechex(255 - \round(\hexdec(\substr($ihex, 2,2))));
        $b = \dechex(255 - \round(\hexdec(\substr($ihex, 4,2))));

        // If the color (rgb) has less than 2 characters, pad with zero
        $padZero = function ($str) {
            return \str_pad($str, 2, 0, \STR_PAD_LEFT);
        };

        // Pad with zero
        $hex = $padZero($r) . $padZero($g) . $padZero($b);

        // Convert hex to decimal
        return \hexdec($hex);
    }

    /**
     * Generates a random number. The higher the $precision, the further from zero the numbers will be generated
     * (not including zero).
     *
     * $precision must be:
     * >= 1
     * > $val
     *
     * @param $val
     * @param int $precision
     * @return int
     */
    protected function rand($val, $precision = 3)
    {
        while( (($r = \rand(-$val, $val)) <= $precision) && $r >= -$precision ) {}

        return $r;
    }
}

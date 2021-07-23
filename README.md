# PHP Captcha

Portable PHP class for creating simple captchas.

## Installation

Install with composer, or use the contents of the **src** folder.

```bash
composer require jacopovalanzano/php-captcha
```

## Usage
Since the captcha is a binary jpeg image, it can be rendered with the "image/jpeg" content-type header.
```php
    // Create a new Captcha
    $captcha = new Captcha("My super difficult to read string.");

    // Add 3 lines over and 3 behind the text,
    // then build the image.
    $captcha->addLinesFront(3)->addLinesBack(3)->build(175,50); // width, height

    // Returns a string containing the captcha passphrase
    $captcha->getPassphrase(); // Returns "My super difficult to read string."

    // Renders the captcha.
    $captcha->out();

```

## Example
A simple example to explain the process of dispatching/retrieving a captcha and its passphrase:
```php
    // This file represents the "www.example.com/get_captcha_image" url that generates our captcha
 
    // ...    

    // A list of words
    $attributes = [ "easy", "green", "digital" ];

    // One more list of words
    $nouns = [ "compare", "dungeon", "clip" ];

    // Compose a phrase
    $words = $attributes[array_rand($attributes)]." ".$nouns[array_rand($nouns)];

    // Create a new captcha with some random words
    $captcha = new Captcha($words);

    // Add 2 lines over and 5 behind the text,
    // then build the image.
    $captcha->addLinesFront(2)->addLinesBack(5)->build(175,50); // width, height

    // Save the captcha to session, so it can be retrieved later... 
    $_SESSION["captcha_passphrase"] = $captcha->getPassphrase();

    // Render the actual captcha image
    $captcha->out();
```

An example of a form you need to validate, like a login form:

```html
<!-- ...  -->

<input type="email" name="email">
<input type="password" name="password">

<!-- Render the actual captcha image using the URL of our captcha-generator (see above): -->
<img id="captcha_image" src="www.example.com/get_captcha_image" alt="captcha">
<input type="text" name="captcha_passphrase">
<input type="submit" value="send">
```

To validate a request from such a login form, one would simply match ```$_SESSION["captcha_passphrase"]```
against the value passed from the input "captcha_passphrase" in the example above, eg:

```php
     // Compare the captcha passphrase with the one submitted
    if($_POST["captcha_passphrase"] !== $_SESSION["captcha_passphrase"]) {
        die("The captchas do not match!");
    }
```

## Contributing
Pull requests are welcome.

## License
[MIT]()

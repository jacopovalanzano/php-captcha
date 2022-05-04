

# PHP Captcha

Portable PHP class for creating simple captchas.
One file. Great for simple projects.

![captcha1](https://i.imgur.com/CakXgDj.gif)

![captcha2](https://i.ibb.co/B6tZc0t/ezgif-4-5d353765b4.gif)

**NOTE**: this captcha is not a final solution to combat bots, but will stop avid and raging attackers.

For comparison, below is an example of a *captcha* used by **tesla.com**:

![tesla-captcha](https://i.imgur.com/tkcogKy.png)

Microsoft ([live.com](live.com)):

![live.com-captcha](https://i.imgur.com/Yy9qxbk.png)

## Installation

Install with composer, or use the contents of the **src** folder.

```bash  
composer require jacopovalanzano/php-captcha  
```  
Requires PHP ^5.4 and [PHP-GD](https://www.php.net/manual/en/book.image.php).

## Usage
The captcha is a binary jpeg image, it can be rendered with the "image/jpeg" content-type header.
```php  
    // Create a new Captcha  
    $captcha = new Captcha("My super difficult to read string.");  
  
    // Add 3 lines over and 3 behind the text,  
    // then build the image.  
    $captcha->linesFront(3)->linesBack(3)->build(175,50); // width, height  
  
    // Returns a string containing the captcha passphrase  
    $captcha->getPassphrase(); // Returns "My super difficult to read string."  
  
    // Renders the captcha.  
    $captcha->out();  
  
```  

## Example
A simple example to explain the process of dispatching/retrieving the captcha and its passphrase:
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
    $captcha->linesFront(2)->linesBack(5)->build(175,50); // width, height  
  
    // Save the captcha passphrase to session, so it can be retrieved later...   
    $_SESSION["captcha_passphrase"] = $captcha->getPassphrase();  
  
    // Render the actual captcha image  
    $captcha->out();  
```  

An example of a form you need to validate, like a login form:

```html  
<!-- ...  -->  
  
<input type="email" name="email">  
<input type="password" name="password">  
  
<!-- Render the actual captcha image using the URL of our captcha-generator (see example above): -->  
<img id="captcha_image" src="www.example.com/get_captcha_image" alt="captcha">  
<input type="text" name="captcha_passphrase">  
<input type="submit" value="send">  
```
Or 
```
echo '<img src="' . $captcha->inline() . '">';
```
Match ```$_SESSION["captcha_passphrase"]```
against the value passed from the input "captcha_passphrase" in the example above, eg:

```php  
    // Compare the captcha passphrase with the one submitted  
    if($_POST["captcha_passphrase"] !== $_SESSION["captcha_passphrase"]) {  
        die("Wrong captcha!");  
    }  
```  

## Tests
Tested with [GNU ocrad](https://www.gnu.org/software/ocrad/) and [Xevil](http://xevil.net)

![xevil](https://i.imgur.com/xnlZsWV.gif)

## Contributing
Pull requests are welcome.

## License
[MIT](https://github.com/jacopovalanzano/php-captcha/blob/main/LICENSE)

#php-sandbox
[![Build Status](https://travis-ci.org/paslandau/php-sandbox.svg?branch=master)](https://travis-ci.org/paslandau/php-sandbox)

A sandbox like implementation for executing PHP code with restriction to certain language constructs (functions etc.)

##Description
Coming soon...

##Basic Usage
Coming soon...

###Examples

See `examples` folder.

##Installation

The recommended way to install php-sandbox is through [Composer](http://getcomposer.org/).

    curl -sS https://getcomposer.org/installer | php

Next, update your project's composer.json file to include php-sandbox:

    {
        "repositories": [ { "type": "composer", "url": "http://packages.myseosolution.de/"} ],
        "minimum-stability": "dev",
        "require": {
             "paslandau/php-sandbox": "dev-master"
        }
    }

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```
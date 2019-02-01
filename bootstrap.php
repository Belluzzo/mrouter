<?php

use Core\App;

require 'vendor/autoload.php';
require 'core/helper.php';

define('ROOT', __DIR__);

try {
    $app = new App();
} catch (Throwable $e) {
    print $e->getMessage();
}
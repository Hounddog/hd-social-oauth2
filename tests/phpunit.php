<?php

error_reporting(E_ALL | E_STRICT);
chdir(dirname(__DIR__));

require __DIR__ . '/HD/Social/OAuth2/Test/Bootstrap.php';

HD\Social\OAuth2\Test\Bootstrap::init();

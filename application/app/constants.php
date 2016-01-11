<?php

defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

define('APPLICATION_DIR', realpath(__DIR__ . '/../app'));
define('CONFIG_DIR', realpath(APPLICATION_DIR . '/config'));
define('CACHE_DIR', realpath(APPLICATION_DIR . '/cache'));
define('LOG_DIR', realpath(APPLICATION_DIR . '/logs'));
define('CONTROLLER_DIR', realpath(__DIR__ . '/../src/Hatchup/Controller'));
define('VIEWS_DIR', realpath(__DIR__ . '/../src/Hatchup/Views'));

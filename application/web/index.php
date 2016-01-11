<?php

require __DIR__ . '/../vendor/autoload.php';

use Hatchup\Routing\Manager as RoutingManager;
use Hatchup\App as App;
use Hatchup\App\Exception as AppException;

$config = 'config.ini';

// application specific constants
require __DIR__ . '/../app/constants.php';

try {
    if (!is_writable(LOG_DIR)) {
        throw new AppException(sprintf('Cannot write to log dir (%s)', LOG_DIR));
    }

    $app = new App(['debug' => (APPLICATION_ENV == 'development')], $config);

    // Initialize the Routing Manager
    $routingManager = new RoutingManager(
        [
            CONTROLLER_DIR
        ],
        CACHE_DIR . '/routing'
    );
    $routingManager->generateRoutes();

    $app->run();

} catch (\Exception $e) {
    if (is_writable(LOG_DIR)) {
        $log = App::openLog('app.error');
        $log->crit($e);
        echo "<h1>A critical error occured, our apologies for the inconvience.</h1>";
    }
    exit(1);
}

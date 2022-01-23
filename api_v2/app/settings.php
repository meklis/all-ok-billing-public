<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => true, // Should be set to false in production
            'stackTraceInErrorResponse' => false,
            'logger' => getGlobalConfigVar('LOGGER_API2'),
            'post_auth_logging' => true,
        ],
    ]);
};

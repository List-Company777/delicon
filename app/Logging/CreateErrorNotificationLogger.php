<?php

namespace App\Logging;

use Monolog\Logger;

class CreateErrorNotificationLogger
{
    public function __invoke(array $config): Logger
    {
        return new Logger('error_notification', [
            new ErrorNotificationHandler(),
        ]);
    }
}

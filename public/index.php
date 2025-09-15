<?php

declare(strict_types=1);

use App\Kernel;
use App\Utils\CmsUtils;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

CmsUtils::setTheme('default');

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};

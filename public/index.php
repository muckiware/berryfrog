<?php
declare(strict_types=1);
/**
 * @package    Berryfrog
 * @copyright  Copyright (c) 2024 by muckiware
 */
use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};

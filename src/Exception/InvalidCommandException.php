<?php

declare(strict_types=1);

namespace Plattry\Process\Exception;

use Exception;

/**
 * Class InvalidCommandException
 * @package Plattry\Process\Exception
 */
class InvalidCommandException extends Exception implements ProgressExceptionInterface
{
}

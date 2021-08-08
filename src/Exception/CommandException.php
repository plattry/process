<?php

declare(strict_types=1);

namespace Plattry\Process\Exception;

use Exception;

/**
 * Class CommandException
 * @package Plattry\Process\Exception
 */
class CommandException extends Exception implements ProgressExceptionInterface
{
}

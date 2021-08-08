<?php

declare(strict_types=1);

namespace Plattry\Process\Exception;

use Exception;

/**
 * Class CommunicationException
 * @package Plattry\Process\Exception
 */
class CommunicationException extends Exception implements ProgressExceptionInterface
{
}

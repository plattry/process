<?php

declare(strict_types=1);

namespace Plattry\Process\Exception;

use Exception;

/**
 * Class OptNotFoundException
 * @package Plattry\Process\Exception
 */
class OptNotFoundException extends Exception implements ProgressExceptionInterface
{
    /**
     * OptNotFoundException constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct("Not found opt item `$name`.");
    }
}

<?php

declare(strict_types=1);

namespace Plattry\Process\Console;

use Plattry\Process\Exception\CommandException;
use Plattry\Process\Exception\InvalidCommandException;
use Plattry\Process\Exception\OptNotFoundException;
use Plattry\Process\Monitor;

/**
 * Class Input
 * @package Plattry\Process\Console
 */
class Input
{
    /**
     * Console input options
     * @var string
     */
    protected const SHORT_OPTIONS = 'o:n:';

    /**
     * Start monitor
     * @var string
     */
    public const OPERATION_START = 'start';

    /**
     * Stop monitor
     * @var string
     */
    public const OPERATION_STOP = 'stop';

    /**
     * Load program
     * @var string
     */
    public const OPERATION_LOAD = 'load';

    /**
     * Unload program
     * @var string
     */
    public const OPERATION_UNLOAD = 'unload';

    /**
     * Reload program
     * @var string
     */
    public const OPERATION_RELOAD = 'reload';

    /**
     * Operation type.
     * @var string
     */
    protected string $operation;

    /**
     * Program name
     * @var string|null
     */
    protected string|null $name = null;

    /**
     * Input constructor.
     * @throws CommandException
     * @throws InvalidCommandException
     * @throws OptNotFoundException
     */
    public function __construct()
    {
        $options = getopt(static::SHORT_OPTIONS);
        [$this->operation, $this->name] = static::parse($options);
    }

    /**
     * Input command format information.
     * @return string
     */
    public static function prompt(): string
    {
        return "Please input command like `php script.php -o start/stop/load/unload/reload [-n program name]`";
    }

    /**
     * Parse console input.
     * @param array $options
     * @return array
     * @throws CommandException
     * @throws InvalidCommandException
     * @throws OptNotFoundException
     */
    protected static function parse(array $options): array
    {
        $operation = $options['o'] ?? null;

        $operation !== static::OPERATION_START &&
        $operation !== static::OPERATION_STOP &&
        $operation !== static::OPERATION_LOAD &&
        $operation !== static::OPERATION_UNLOAD &&
        $operation !== static::OPERATION_RELOAD &&
        throw new InvalidCommandException(sprintf("%s is an invalid operation.", $operation));

        $name = $options['n'] ?? null;

        !is_null($name) && !in_array($name, array_keys(Monitor::getInstance()->getOpt())) &&
        throw new InvalidCommandException(sprintf("%s is an invalid object.", $name));

        return [$operation, $name];
    }

    /**
     * Get operation type.
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * Get program name.
     * @return string|null
     */
    public function getName(): string|null
    {
        return $this->name;
    }
}

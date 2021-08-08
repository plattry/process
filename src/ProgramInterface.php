<?php

declare(strict_types=1);

namespace Plattry\Process;

/**
 * Interface ProgramInterface
 * @package Plattry\Process
 */
interface ProgramInterface
{
    /**
     * ProgramInterface constructor.
     * @param string $name
     * @param string $command
     */
    public function __construct(string $name, string $command);

    /**
     * Get the program name.
     * @return string
     */
    public function getName(): string;

    /**
     * Get the program start command.
     * @return string
     */
    public function getCommand(): string;

    /**
     * Get the program stop signal.
     * @return int
     */
    public function getStopSignal(): int;

    /**
     * Get the program progress number.
     * @return int
     */
    public function getNumber(): int;

    /**
     * Get the program priority.
     * @return int
     */
    public function getPriority(): int;

    /**
     * Whether the program starts automatically
     * @return bool
     */
    public function isAutoStart(): bool;

    /**
     * Whether the program restarts automatically
     * @return bool
     */
    public function isAutoRestart(): bool;
}

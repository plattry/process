<?php

declare(strict_types=1);

namespace Plattry\Process;

/**
 * Class Program
 * @package Plattry\Process
 */
class Program implements ProgramInterface
{
    /**
     * Program name
     * @var string
     */
    protected string $name;

    /**
     * Start command
     * @var string
     */
    protected string $command;

    /**
     * Stop signal
     * @var int
     */
    protected int $stop_signal = SIGKILL;

    /**
     * Process number
     * @var int
     */
    protected int $number = 1;

    /**
     * Program priority
     * @var int
     */
    protected int $priority = 1;

    /**
     * Auto-start status
     * @var bool
     */
    protected bool $auto_start = true;

    /**
     * Auto-restart status
     * @var bool
     */
    protected bool $auto_restart = false;

    /**
     * @inheritDoc
     */
    public function __construct(string $name, string $command)
    {
        $this->name = $name;
        $this->command = $command;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * Get stop signal.
     * @return int
     */
    public function getStopSignal(): int
    {
        return $this->stop_signal;
    }

    /**
     * Set stop signal.
     * @param int $stop_signal
     * @return void
     */
    public function setStopSignal(int $stop_signal): void
    {
        $this->stop_signal = $stop_signal;
    }

    /**
     * @inheritDoc
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Get progress number.
     * @param int $number
     * @return void
     */
    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Set program priority.
     * @param int $priority
     * @return void
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @inheritDoc
     */
    public function isAutoStart(): bool
    {
        return $this->auto_start;
    }

    /**
     * Set auto-start status.
     * @param bool $auto_start
     * @return void
     */
    public function setAutoStart(bool $auto_start): void
    {
        $this->auto_start = $auto_start;
    }

    /**
     * @inheritDoc
     */
    public function isAutoRestart(): bool
    {
        return $this->auto_restart;
    }

    /**
     * Set auto-restart status.
     * @param bool $auto_restart
     * @return void
     */
    public function setAutoRestart(bool $auto_restart): void
    {
        $this->auto_restart = $auto_restart;
    }
}

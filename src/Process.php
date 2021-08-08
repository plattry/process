<?php

declare(strict_types=1);

namespace Plattry\Process;

use Plattry\Process\Console\Command;
use Plattry\Utils\Debug;
use Throwable;

/**
 * Class Process
 * @package Plattry\Process
 */
class Process implements ProcessInterface
{
    /**
     * Process pool
     * @var Process[]
     */
    protected static array $pools = [];

    /**
     * Process id
     * @var int|null
     */
    private int|null $id = null;

    /**
     * Program
     * @var ProgramInterface
     */
    protected ProgramInterface $program;

    /**
     * Process sentry.
     * @var \EvTimer
     */
    protected \EvTimer $sentry;

    /**
     * Process killer.
     * @var \EvTimer
     */
    protected \EvTimer $killer;

    /**
     * @inheritDoc
     */
    public function __construct(ProgramInterface $program)
    {
        static::$pools[spl_object_id($this)] = $this;

        $this->program = $program;
        $this->sentry = \EvTimer::createStopped(0, 2, [$this, 'watch']);
        $this->killer = \EvTimer::createStopped(0, 2, [$this, 'kill']);
    }

    /**
     * Get all progress.
     * @return Process[]
     */
    public static function getPool(): array
    {
        return static::$pools;
    }

    /**
     * @inheritDoc
     */
    public function getProgram(): ProgramInterface
    {
        return $this->program;
    }

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        $this->id = (int)Command::start($this->program->getCommand());;
        $this->sentry->start();
    }

    /**
     * @inheritDoc
     */
    public function stop(): void
    {
        $this->sentry->stop();

        Command::stop($this->program->getStopSignal(), $this->id);

        $this->killer->start();
    }

    /**
     * Watch progress status.
     * @return void
     */
    protected function watch(): void
    {
        try {
            $result = (int)Command::check($this->id);
            if ($result !== $this->id && $this->program->isAutoRestart()) {
                $this->start();
            } else {
                unset(static::$pools[spl_object_id($this)]);
            }
        } catch (Throwable $t) {
            Debug::handleThrow($t);
        }
    }

    /**
     * Kill progress and clear resource.
     * @return void
     */
    protected function kill(): void
    {
        try {
            Command::kill($this->id);
        } catch (Throwable $t) {
            Debug::handleThrow($t);
        } finally {
            $this->killer->stop();
            $this->id = null;
            unset(static::$pools[spl_object_id($this)]);
        }
    }
}

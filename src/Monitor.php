<?php

declare(strict_types=1);

namespace Plattry\Process;

use Closure;
use Plattry\Process\Comm\QueueTrait;
use Plattry\Process\Comm\SignalTrait;
use Plattry\Process\Console\Command;
use Plattry\Process\Console\Input;
use Plattry\Process\Exception\CommandException;
use Plattry\Process\Exception\CommunicationException;
use Plattry\Process\Exception\OptNotFoundException;
use Plattry\Utils\Debug;
use Throwable;

/**
 * Class Monitor
 * @package Plattry\Process
 */
class Monitor implements MonitorInterface
{
    use QueueTrait;

    use SignalTrait;

    /**
     * Pid file path
     * @var string
     */
    const PATH = '/tmp';

    /**
     * Process name
     * @var string
     */
    const NAME = 'plattry-monitor';

    /**
     * Monitor object
     * @var Monitor
     */
    protected static Monitor $instance;

    /**
     * Main progress id
     * @var int
     */
    protected int $id;

    /**
     * Is main progress
     * @var bool
     */
    protected bool $is_main;

    /**
     * Reclaimer
     * @var \EvTimer
     */
    protected \EvTimer $reclaimer;

    /**
     * Program array
     * @var ProgramInterface[]
     */
    protected array $opt = [];

    /**
     * Monitor constructor.
     */
    private function __construct()
    {
    }

    /**
     * Monitor cloner.
     * @return void
     */
    private function __clone(): void
    {
    }

    /**
     * Get Monitor object.
     * @return static
     * @throws CommandException
     */
    public static function getInstance(): static
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
            static::$instance->id = static::$instance->initPidFile();
            static::$instance->is_main = static::$instance->id === posix_getpid();
            static::$instance->reclaimer = \EvTimer::createStopped(0, 2, [static::$instance, 'recycle']);
            cli_set_process_title(static::$instance::NAME);
        }

        return static::$instance;
    }

    /**
     * Is resource are being recycling.
     * @return bool
     */
    protected function isRecycling(): bool
    {
        return $this->reclaimer->is_active;
    }

    /**
     * @inheritDoc
     */
    public function hasOpt(string $name): bool
    {
        return isset($this->opt[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getOpt(string $name = null): array|ProgramInterface
    {
        if (is_null($name)) {
            return $this->opt;
        }

        !isset($this->opt[$name]) &&
        throw new OptNotFoundException($name);

        return $this->opt[$name];
    }

    /**
     * @inheritDoc
     */
    public function setOpt(ProgramInterface $program): void
    {
        $this->opt[$program->getName()] = $program;
    }

    /**
     * Get pid file name.
     * @return string
     */
    protected function getPidFile(): string
    {
        return sprintf("%s/%s.pid", static::PATH, static::NAME);
    }

    /**
     * Init pid file.
     * @return int
     * @throws CommandException
     */
    protected function initPidFile(): int
    {
        $pid = null;
        $file = $this->getPidFile();
        if (file_exists($file)) {
            $content = (int)file_get_contents($file);
            $result = (int)Command::check($content);
            if ($content === $result) $pid = $result;
        }

        if (is_null($pid)) {
            $pid = posix_getpid();
            file_put_contents($file, $pid, LOCK_EX);
        }

        return $pid;
    }

    /**
     * Recycle progress.
     * @return void
     */
    protected function recycle(): void
    {
        try {
            if (empty(Process::getPool()))
                \Ev::stop();
        } catch (Throwable $t) {
            Debug::handleThrow($t);
        }
    }

    /**
     * Start monitor and load all auto-start program.
     * @return void
     */
    protected function start(): void
    {
        $this->reclaimer->stop();
        $this->loadProgram();
    }

    /**
     * Unload all program and stop monitor.
     * @return void
     */
    protected function stop(): void
    {
        $this->unloadProgram();
        $this->reclaimer->start();
    }

    /**
     * Load the program.
     * @param string|null $name
     * @return void
     */
    protected function loadProgram(string|null $name = null): void
    {
        if ($this->isRecycling())
            return;

        if (is_null($name)) {
            foreach ($this->opt as $program) {
                if ($program->isAutoStart())
                    $this->loadProgram($program->getName());
            }
        } else {
            $aliveNum = 0;
            foreach (Process::getPool() as $progress) {
                if ($progress->getProgram()->getName() !== $name)
                    continue;

                $aliveNum++;
            }

            $loadNum = $this->opt[$name]->getNumber() - $aliveNum;
            for ($i = 0; $i < $loadNum; $i++) {
                $progress = new Process($this->opt[$name]);
                $progress->start();
            }
        }
    }

    /**
     * Unload the program.
     * @param string|null $name
     * @return void
     */
    protected function unloadProgram(string|null $name = null): void
    {
        if ($this->isRecycling())
            return;

        if (is_null($name)) {
            foreach ($this->opt as $program) {
                $this->unloadProgram($program->getName());
            }
        } else {
            foreach (Process::getPool() as $progress) {
                if ($progress->getProgram()->getName() !== $name)
                    continue;

                $progress->stop();
            }
        }
    }

    /**
     * Reload the program.
     * @param string|null $name
     * @return void
     */
    protected function reloadProgram(string|null $name = null): void
    {
        if ($this->isRecycling())
            return;

        if (is_null($name)) {
            foreach ($this->opt as $program) {
                if ($program->isAutoRestart())
                    $this->reloadProgram($program->getName());
            }
        } else {
            $newer = [];
            for ($i = 0; $i < $this->opt[$name]->getNumber(); $i++) {
                $progress = new Process($this->opt[$name]);
                $progress->start();
                $newer[] = spl_object_id($progress);
            }

            foreach (Process::getPool() as $progress) {
                if ($progress->getProgram()->getName() !== $name)
                    continue;

                if (in_array(spl_object_id($progress), $newer))
                    continue;

                $progress->stop();
            }
        }
    }

    /**
     * Resolve the input command.
     * @param Input $input
     * @return Closure
     */
    protected function resolve(Input $input): Closure
    {
        return match ($input->getOperation()) {
            Input::OPERATION_START => fn () => $this->start(),
            Input::OPERATION_STOP => fn () => $this->stop(),
            Input::OPERATION_LOAD => fn () => $this->loadProgram($input->getName()),
            Input::OPERATION_UNLOAD => fn () => $this->unloadProgram($input->getName()),
            Input::OPERATION_RELOAD => fn () => $this->reloadProgram($input->getName())
        };
    }

    /**
     * Run monitor.
     * @return void
     * @throws CommunicationException
     */
    public function run(): void
    {
        try {
            $input = new Input();
        } catch (Throwable $t) {
            Debug::handleThrow($t);
            Debug::handleMessage(Input::prompt());
            exit(0);
        }

        if ($this->is_main) {
            $this->installReceiver();
            $this->installSignal();

            try {
                $callback = $this->resolve($input);
                $callback();
            } catch (Throwable $t) {
                Debug::handleThrow($t);
            } finally {
                \Ev::run();
            }
        } else {
            $this->sendMessage($input);
        }
    }

    /**
     * Monitor destructor.
     */
    public function __destruct()
    {
        $file = $this->getPidFile();
        $this->is_main && unlink($file);
    }
}

<?php

declare(strict_types=1);

namespace Plattry\Process\Comm;

use Plattry\Utils\Debug;
use Throwable;

/**
 * Trait SignalTrait
 * @package Plattry\Process\Comm
 * @method stop()
 * @method loadProgram(string|null $name = null)
 * @method unloadProgram(string|null $name = null)
 * @method reloadProgram(string|null $name = null)
 */
trait SignalTrait
{
    /**
     * SIGQUIT handler
     * @var \EvSignal
     */
    protected \EvSignal $sigQuit;

    /**
     * SIGCONT handler
     * @var \EvSignal
     */
    protected \EvSignal $sigCont;

    /**
     * SIGHUP handler
     * @var \EvSignal
     */
    protected \EvSignal $sigHup;

    /**
     * SIGUSR1 handler
     * @var \EvSignal
     */
    protected \EvSignal $sigUsr1;

    /**
     * Install signal handler.
     * @return void
     */
    protected function installSignal(): void
    {
        !isset($this->sigQuit) &&
        $this->sigQuit = new \EvSignal(SIGQUIT, [$this, 'handleSigQuit']);

        !isset($this->sigCont) &&
        $this->sigCont = new \EvSignal(SIGCONT, [$this, 'handleSigCont']);

        !isset($this->sigHup) &&
        $this->sigHup = new \EvSignal(SIGHUP, [$this, 'handleSigHup']);

        !isset($this->sigUsr1) &&
        $this->sigUsr1 = new \EvSignal(SIGUSR1, [$this, 'handleSigUsr1']);
    }

    /**
     * Handle SIGQUIT.
     * @return void
     */
    protected function handleSigQuit(): void
    {
        try {
            $this->stop();
        } catch (Throwable $t) {
            Debug::handleThrow($t);
        }
    }

    /**
     * Handle SIGCONT.
     * @return void
     */
    protected function handleSigCont(): void
    {
        try {
            $this->loadProgram();
        } catch (Throwable $t) {
            Debug::handleThrow($t);
        }
    }

    /**
     * Handle SIGHUP.
     * @return void
     */
    protected function handleSigHup(): void
    {
        try {
            $this->unloadProgram();
        } catch (Throwable $t) {
            Debug::handleThrow($t);
        }
    }

    /**
     * Handle SIGUSR1.
     * @return void
     */
    protected function handleSigUsr1(): void
    {
        try {
            $this->reloadProgram();
        } catch (Throwable $t) {
            Debug::handleThrow($t);
        }
    }
}

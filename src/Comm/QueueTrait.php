<?php

declare(strict_types=1);

namespace Plattry\Process\Comm;

use Plattry\Process\Console\Input;
use Plattry\Process\Exception\CommunicationException;
use Plattry\Utils\Debug;
use SysvMessageQueue;
use Throwable;

/**
 * Trait QueueTrait
 * @package Plattry\Process\Comm
 * @property int $id
 * @method getPidFile(): string
 * @method loadProgram(string|null $name = null): void
 * @method unloadProgram(string|null $name = null): void
 * @method reloadProgram(string|null $name = null): void
 */
trait QueueTrait
{
    /**
     * Message receiver
     * @var \EvTimer
     */
    protected \EvTimer $receiver;

    /**
     * Get a SysvMessageQueue object.
     * @return SysvMessageQueue
     * @throws CommunicationException
     */
    protected function generateQueue(): SysvMessageQueue
    {
        $file = $this->getPidFile();
        $stat = stat($file);
        $msg_key = (int)sprintf('%u',
            ($stat['ino'] & 0xffff) | (($stat['dev'] & 0xff) << 16) | (($this->id & 0xff) << 24)
        );

        $queue = msg_get_queue($msg_key);
        $queue === false &&
        throw new CommunicationException("An error occur while getting a queue.");

        return $queue;
    }

    /**
     * Receive a message from queue.
     * @return void
     */
    protected function receiveMessage(): void
    {
        try {
            $queue = $this->generateQueue();
            $stat = msg_stat_queue($queue);

            if ($stat['msg_qnum'] > 0) {
                msg_receive($queue, 1, $type, 16384, $message, true, 0, $error);
                $callback = $this->resolve($message);
                $callback();
            }
        } catch (Throwable $t) {
            Debug::handleThrow($t);
        }
    }

    /**
     * Send a message to queue.
     * @param Input $input
     * @throws CommunicationException
     * @return void
     */
    protected function sendMessage(Input $input): void
    {
        $queue = $this->generateQueue();
        msg_send($queue, 1, $input, true, true, $code);
    }

    /**
     * Install message receiver.
     * @return void
     */
    protected function installReceiver(): void
    {
        !isset($this->receiver) &&
        $this->receiver = new \EvTimer(0, 2, [$this, 'receiveMessage']);
    }
}

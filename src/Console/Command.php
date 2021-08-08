<?php

declare(strict_types=1);

namespace Plattry\Process\Console;

use Plattry\Process\Exception\CommandException;

/**
 * Class Command
 * @package Plattry\Process\Console
 */
class Command
{
    /**
     * Execute start command.
     * @param string $command
     * @return string
     * @throws CommandException
     */
    public static function start(string $command): string
    {
        $command = sprintf("%s > /dev/null 2>&1 & echo $!", $command);

        return static::exec($command);
    }

    /**
     * Execute stop command.
     * @param int $signal
     * @param int $pid
     * @return string
     * @throws CommandException
     */
    public static function stop(int $signal, int $pid): string
    {
        $command = sprintf("kill -%d %d", $signal, $pid);

        return static::exec($command);
    }

    /**
     * Execute kill command.
     * @param int $pid
     * @return string
     * @throws CommandException
     */
    public static function kill(int $pid): string
    {
        $command = sprintf("kill -%d %d", SIGKILL, $pid);

        return static::exec($command);
    }

    /**
     * Execute check command.
     * @param int $pid
     * @return string
     * @throws CommandException
     */
    public static function check(int $pid): string
    {
        $command = sprintf("ps aux | grep %d | grep -v grep | awk '{print $1}'", $pid);

        return static::exec($command);
    }

    /**
     * Execute command.
     * @param string $command
     * @return string
     * @throws CommandException
     */
    public static function exec(string $command): string
    {
        $lastLine = exec($command, $output, $code);

        if ($lastLine === false || $code !== 0) {
            throw new CommandException(sprintf(
                "An error occur while executing %s and output: %s.",
                $command, implode(PHP_EOL, $output)
            ));
        }

        return $lastLine;
    }
}

<?php

declare(strict_types=1);

namespace Plattry\Process;

use Plattry\Process\Exception\OptNotFoundException;

/**
 * Interface MonitorInterface
 * @package Plattry\Process
 */
interface MonitorInterface
{
    /**
     * Is there any program of the specified name in the opt.
     * @param string $name
     * @return bool
     */
    public function hasOpt(string $name): bool;

    /**
     * Get the program in the opt.
     * @param string|null $name
     * @return ProgramInterface[]|ProgramInterface
     * @throws OptNotFoundException
     */
    public function getOpt(string $name = null): array|ProgramInterface;

    /**
     * Set a program to opt.
     * @param ProgramInterface $program
     * @return void
     */
    public function setOpt(ProgramInterface $program): void;
}

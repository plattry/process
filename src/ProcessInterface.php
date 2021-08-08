<?php

declare(strict_types=1);

namespace Plattry\Process;

/**
 * Interface ProcessInterface
 * @package Plattry\Process
 */
interface ProcessInterface
{
    /**
     * ProcessInterface constructor.
     * @param ProgramInterface $program
     */
    public function __construct(ProgramInterface $program);

    /**
     * Get the program hosted by the progress.
     * @return ProgramInterface
     */
    public function getProgram(): ProgramInterface;

    /**
     * Start progress.
     * @return void
     */
    public function start(): void;

    /**
     * Stop progress.
     * @return void
     */
    public function stop(): void;
}

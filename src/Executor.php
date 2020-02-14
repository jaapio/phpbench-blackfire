<?php

declare(strict_types=1);

namespace Jaapio\Blackfire;

use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Executor\Benchmark\TemplateExecutor;

final class Executor extends TemplateExecutor
{
    public function __construct(Launcher $launcher)
    {
        parent::__construct($launcher, __DIR__ . '/template/blackfire.template');
    }
}

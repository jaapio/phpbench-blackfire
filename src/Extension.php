<?php

declare(strict_types=1);

namespace Jaapio\Blackfire;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;

final class Extension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function load(Container $container)
    {
        $container->register(
            'jaapio.blackfire_executor',
            static function (Container $container) : Executor {
                return new Executor(
                    $container->get('benchmark.remote.launcher')
                );
            },
            ['benchmark_executor' => ['name' => 'blackfire']]
        );

        $container->mergeParameter(
            'executors',
            [
                'blackfire' => [
                     'executor' => 'blackfire',
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getDefaultConfig()
    {
        return [];
    }
}

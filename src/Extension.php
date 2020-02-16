<?php

declare(strict_types=1);

namespace Jaapio\Blackfire;

use Blackfire\ClientConfiguration;
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
                    $container->get('benchmark.remote.launcher'),
                    $container->get('jaapio.blackfire.progress_logger'),
                    $container->get('jaapio.blackfire.config')
                );
            },
            ['benchmark_executor' => ['name' => 'blackfire']]
        );

        $container->register('jaapio.blackfire.progress_logger', function (Container $container) {
            $loggers = $container->getServiceIdsForTag('progress_logger');

            foreach ($loggers as $serviceId => $value) {
                if ($value['name'] === $container->getParameter('progress')) {
                    $loggerId = $serviceId;
                    break;
                }
            }

            return new Logger(
                $container->get($loggerId),
                $container->get('jaapio.blackfire.config')
            );
        }, ['progress_logger' => ['name' => 'blackfire']]);

        $container->register('jaapio.blackfire.config', function (Container $container) {
            $config = $container->getParameter('blackfire')['config'];
            $env = $container->getParameter('blackfire')['env'];

            $clientConfig = new ClientConfiguration();
            if ($config !== null) {
                $clientConfig = ClientConfiguration::createFromFile($config);
            }

            $clientConfig->setEnv($env);

            return $clientConfig;
        });

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
        return [
            'blackfire' => [
                'config' => null,
                'env' => null,
            ]
        ];
    }
}

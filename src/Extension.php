<?php

declare(strict_types=1);

namespace Jaapio\Blackfire;

use Blackfire\ClientConfiguration;
use Jaapio\Blackfire\Environment\Provider\GithubAction;
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
            $blackfireConfig = $container->getParameter('blackfire');

            $config = $blackfireConfig['config'] ?? null;
            $env = $blackfireConfig['env'] ?? null;

            $clientConfig = new ClientConfiguration();
            if ($config !== null) {
                $clientConfig = ClientConfiguration::createFromFile($config);
            }

            $clientConfig->setEnv($env);

            return $clientConfig;
        });

        $container->register(
            'jaapio.blackfire.environment.github',
            function (Container $container) {
                return new GithubAction();
            },
            ['environment_provider' => []]
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
            ],
        ];
    }
}

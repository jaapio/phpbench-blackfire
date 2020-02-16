<?php

declare(strict_types=1);

namespace Jaapio\Blackfire;

use Blackfire\ClientConfiguration;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Executor\Benchmark\TemplateExecutor;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;

final class Executor extends TemplateExecutor
{
    private const PHP_OPTION_MAX_EXECUTION_TIME = 'max_execution_time';

    private const TEMPLATE = __DIR__ . '/template/blackfire.template';
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Launcher
     */
    private $launcher;
    /**
     * @var ClientConfiguration
     */
    private $clientConfiguration;

    public function __construct(Launcher $launcher, Logger $logger, ClientConfiguration $clientConfiguration)
    {
        parent::__construct($launcher, self::TEMPLATE);
        $this->logger = $logger;
        $this->launcher = $launcher;
        $this->clientConfiguration = $clientConfiguration;
    }

    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config): void
    {
        $tokens = [
            'class' => $subjectMetadata->getBenchmark()->getClass(),
            'file' => $subjectMetadata->getBenchmark()->getPath(),
            'subject' => $subjectMetadata->getName(),
            'revolutions' => $iteration->getVariant()->getRevolutions(),
            'beforeMethods' => var_export($subjectMetadata->getBeforeMethods(), true),
            'afterMethods' => var_export($subjectMetadata->getAfterMethods(), true),
            'parameters' => var_export($iteration->getVariant()->getParameterSet()->getArrayCopy(), true),
            'warmup' => $iteration->getVariant()->getWarmup() ?: 0,
            'scenario' => serialize($this->logger->getScenario()),
            'blackfire_config' => serialize($this->clientConfiguration)
        ];

        $payload = $this->launcher->payload(self::TEMPLATE, $tokens);
        $this->launch($payload, $iteration, $config);
    }

    private function launch(Payload $payload, Iteration $iteration, Config $options)
    {
        $payload->mergePhpConfig(array_merge(
            [
                self::PHP_OPTION_MAX_EXECUTION_TIME => 0,
            ],
            $options[self::OPTION_PHP_CONFIG] ?? []
        ));

        $result = $payload->launch();

        if (isset($result['buffer']) && $result['buffer']) {
            throw new \RuntimeException(sprintf(
                'Benchmark made some noise: %s',
                $result['buffer']
            ));
        }

        $iteration->setResult(new TimeResult($result['time']));
        $iteration->setResult(MemoryResult::fromArray($result['mem']));
    }
}

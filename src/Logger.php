<?php

declare(strict_types=1);

namespace Jaapio\Blackfire;

use Blackfire\Build\Scenario;
use Blackfire\Client;
use Blackfire\ClientConfiguration;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\Progress\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Logger implements LoggerInterface
{
    /**
     * @var Client
     */
    private $blackfire;

    /**
     * @var \Blackfire\Build\Build
     */
    private $build;

    /**
     * @var Scenario
     */
    private $scenario;

    /**
     * @var LoggerInterface
     */
    private $innerLogger;

    public function __construct(LoggerInterface $innerLogger, ClientConfiguration $clientConfiguration)
    {
        $this->blackfire = new Client($clientConfiguration);
        $this->innerLogger = $innerLogger;
    }

    /**
     * @inheritDoc
     */
    public function benchmarkEnd(Benchmark $benchmark)
    {
        $this->innerLogger->benchmarkEnd($benchmark);
    }

    /**
     * @inheritDoc
     */
    public function benchmarkStart(Benchmark $benchmark)
    {
        $this->innerLogger->benchmarkStart($benchmark);
    }

    /**
     * @inheritDoc
     */
    public function subjectEnd(Subject $subject)
    {
        $this->innerLogger->subjectEnd($subject);
    }

    /**
     * @inheritDoc
     */
    public function subjectStart(Subject $subject)
    {
        $this->innerLogger->subjectStart($subject);
    }

    /**
     * @inheritDoc
     */
    public function variantEnd(Variant $variant)
    {
        $this->innerLogger->variantEnd($variant);
    }

    /**
     * @inheritDoc
     */
    public function variantStart(Variant $variant)
    {
        $this->innerLogger->variantStart($variant);
    }

    /**
     * @inheritDoc
     */
    public function iterationEnd(Iteration $iteration)
    {
        $this->blackfire->closeScenario($this->scenario);
        $this->innerLogger->iterationEnd($iteration);
    }

    /**
     * @inheritDoc
     */
    public function iterationStart(Iteration $iteration)
    {
        $this->scenario = $this->blackfire->startScenario($this->build, [
            'title' => sprintf(
                '%s::%s #%d',
                    $iteration->getVariant()->getSubject()->getBenchmark()->getClass(),
                    $iteration->getVariant()->getSubject()->getName(),
                    $iteration->getIndex()
            ),
            'metadata' => [
                'variant' => $iteration->getVariant()->getParameterSet()->getName(),
                'iteration' => $iteration->getIndex(),
            ]
        ]);

        $this->innerLogger->iterationStart($iteration);
    }

    /**
     * @inheritDoc
     */
    public function retryStart($rejectionCount)
    {
        $this->innerLogger->retryStart($rejectionCount);
    }

    /**
     * @inheritDoc
     */
    public function startSuite(Suite $suite)
    {
        $this->build = $this->blackfire->startBuild(null, ['title' => (string) $suite->getTag()]);
        $this->innerLogger->startSuite($suite);
    }

    /**
     * @inheritDoc
     */
    public function endSuite(Suite $suite)
    {
        $this->blackfire->closeBuild($this->build);
        $this->build = null;
        $this->innerLogger->endSuite($suite);
    }

    public function setOutput(OutputInterface $output)
    {
        $this->innerLogger->setOutput($output);
    }

    public function getScenario() : Scenario
    {
        return $this->scenario;
    }
}

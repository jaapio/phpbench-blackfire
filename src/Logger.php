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

    /**
     * @var Suite
     */
    private $suite;

    /**
     * @var OutputInterface
     */
    private $output;

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
        $title = sprintf(
            '%s::%s #%d',
            $iteration->getVariant()->getSubject()->getBenchmark()->getClass(),
            $iteration->getVariant()->getSubject()->getName(),
            $iteration->getIndex()
        );

        $externalId = null;
        $externalParentId = null;


        if ($this->getExternalId() !== null) {
            $externalId = sprintf('%s:%s', $this->getExternalId(), md5($title));
        }

        if ($this->getExternalParentId() !== null) {
            $externalParentId = sprintf('%s:%s', $this->getExternalParentId(), md5($title));
            $this->output->writeln('Starting scenario with parent id ' . $externalParentId);
        }

        $this->scenario = $this->blackfire->startScenario(
            $this->build,
            array_filter([
                'title' => $title,
                'external_id' => $externalId,
                'external_parent_id' => $externalParentId,
                'metadata' => [
                    'variant' => $iteration->getVariant()->getParameterSet()->getName(),
                    'iteration' => $iteration->getIndex(),
                ],
            ])
        );

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
        $this->suite = $suite;
        $this->output->writeln('Starting build for ' . $this->getExternalId());

        $this->build = $this->blackfire->startBuild(
            null,
            array_filter([
                'title' => (string) $suite->getTag(),
                'external_id' => $this->getExternalId(),
            ])
        );
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
        $this->output = $output;
        $this->innerLogger->setOutput($output);
    }

    public function getScenario() : Scenario
    {
        return $this->scenario;
    }

    private function getExternalId()
    {
        $vcsEnv = $this->getGithubEnv();
        return $vcsEnv !== null ? $vcsEnv['pull_request_head_sha'] : null;
    }

    private function getExternalParentId()
    {
        $vcsEnv = $this->getGithubEnv();
        return $vcsEnv !== null ? $vcsEnv['pull_request_base_sha'] : null;
    }

    private function getGithubEnv(): ?array
    {
        if (!array_key_exists('github', $this->suite->getEnvInformations())) {
            return null;
        }

        return $this->suite->getEnvInformations()['github'];
    }
}

<?php

declare(strict_types=1);

namespace Jaapio\Blackfire\Environment\Provider;

use PhpBench\Environment\Information;
use PhpBench\Environment\ProviderInterface;

final class GithubAction implements ProviderInterface
{

    /**
     * @inheritDoc
     */
    public function isApplicable()
    {
        return $this->isPush() || $this->isPullRequest();
    }

    /**
     * @inheritDoc
     */
    public function getInformation()
    {
        $head = null;
        $base = null;

        $event = $this->getEventData();

        if ($this->isPush()) {
            $head = $event['after'];
            $base = $event['before'];
        }

        if ($this->isPullRequest()) {
            $head = $event['pull_request_head_sha'];
            $base = $event['pull_request_base_sha'];
        }

        return new Information(
            'github',
            [
                'base' => $base,
                'head' => $head,
                'event' => $event
            ]
        );
    }

    private function isPullRequest() : bool
    {
        return getenv('GITHUB_EVENT_NAME') === 'pull_request';
    }

    private function isPush() : bool
    {
        return getenv('GITHUB_EVENT_NAME') === 'push';
    }

    /**
     * @return mixed
     */
    private function getEventData() : array
    {
        return json_decode(
            file_get_contents(
                getenv('GITHUB_EVENT_PATH')
            ),
            true
        );
    }

}

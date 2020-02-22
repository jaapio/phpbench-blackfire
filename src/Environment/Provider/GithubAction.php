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
        return getenv('GITHUB_EVENT_PATH') !== false;
    }

    /**
     * @inheritDoc
     */
    public function getInformation()
    {
        return new Information(
            'github',
            json_decode(
                file_get_contents(
                    getenv('GITHUB_EVENT_PATH')
                ),
                true
            )
        );
    }
}

<?php

declare(strict_types=1);

namespace Support\Search\Scout\Console;

use OpenSearch\Client;

/**
 * Temporarily disables auto-refresh on an OpenSearch index during bulk import,
 * then restores the prior value and forces a one-shot refresh afterward.
 *
 * Usage:
 *   $mgr = new IndexRefreshManager($client, $indexName);
 *   $mgr->suspend();
 *   try {
 *       // bulk index
 *   } finally {
 *       $mgr->restore();
 *   }
 */
final class IndexRefreshManager
{
    private string $prior;

    public function __construct(
        private readonly Client $client,
        private readonly string $index,
    ) {}

    /**
     * Disable auto-refresh and record the prior interval for restoration.
     */
    public function suspend(): void
    {
        $settings = $this->client->indices()->getSettings(['index' => $this->index]);

        $this->prior = $settings[$this->index]['settings']['index']['refresh_interval'] ?? '1s';

        $this->client->indices()->putSettings([
            'index' => $this->index,
            'body' => ['index' => ['refresh_interval' => '-1']],
        ]);
    }

    /**
     * Restore the prior refresh interval and immediately trigger one refresh
     * so newly indexed documents are visible without waiting for the interval.
     */
    public function restore(): void
    {
        $this->client->indices()->putSettings([
            'index' => $this->index,
            'body' => ['index' => ['refresh_interval' => $this->prior]],
        ]);

        $this->client->indices()->refresh(['index' => $this->index]);
    }
}

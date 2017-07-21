<?php

namespace Ipunkt\LaravelIndexer\Jobs\Solr;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Solarium\Client;
use Solarium\Exception\ExceptionInterface;
use Solarium\Exception\HttpException;

class Optimize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    public $cacheKey;

    /**
     * Optimize constructor.
     * @param string $cacheKey
     */
    public function __construct(string $cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * Execute the job.
     *
     * @param Repository $cache
     * @param Client $client
     * @return void
     * @throws \RuntimeException
     */
    public function handle(Repository $cache, Client $client)
    {
        if ($cache->has($this->cacheKey)) {
            /** @var Carbon $nextPossibleOptimize */
            $nextPossibleOptimize = $cache->get($this->cacheKey);
            if ($nextPossibleOptimize instanceof Carbon
                && $nextPossibleOptimize->isFuture()) {
                // another modification call was done, so another optimize job was already set up
                // so kill this job without optimizing
                $this->job->delete();
                return;
            }
        }

        try {
            $update = $client->createUpdate();
            $update->addOptimize(true, false);
            $result = $client->update($update);
        } catch (HttpException $e) {
            $errorMessage = $e->getMessage();
            if (+$e->getCode() === 400) {
                $error = json_decode($e->getBody(), true);
                $errorMessage = array_get($error, 'error.msg', $e->getMessage());

                $this->job->failed($e);
            }
            throw new \RuntimeException($errorMessage, $e->getCode(), $e);
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('No optimize command could be sent to solr', $e->getCode(), $e);
        }
    }
}

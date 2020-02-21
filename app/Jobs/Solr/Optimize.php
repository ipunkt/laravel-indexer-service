<?php

namespace Ipunkt\LaravelIndexer\Jobs\Solr;

use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
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
		} catch (HttpException $exception) {
			$errorMessage = $exception->getMessage();
			if (+$exception->getCode() === 400) {
				$error = json_decode($exception->getBody(), true);
				$errorMessage = array_get($error, 'error.msg', $exception->getMessage());
			}
			$this->deleteJob($exception);
			throw new RuntimeException($errorMessage, $exception->getCode(), $exception);
		} catch (ExceptionInterface $exception) {
			$this->deleteJob($exception);
			throw new RuntimeException('No optimize command could be sent to solr', $exception->getCode(), $exception);
		} catch (Exception $exception) {
			$this->deleteJob($exception);
		}
	}

	private function deleteJob($exception)
	{
		$this->job->failed($exception);
		$this->job->delete();
	}
}

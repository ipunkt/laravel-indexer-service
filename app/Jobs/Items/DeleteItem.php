<?php

namespace Ipunkt\LaravelIndexer\Jobs\Items;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Solarium\Client;
use Solarium\Exception\ExceptionInterface;
use Solarium\Exception\HttpException;

class DeleteItem implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var string|int
	 */
	private $id;

	/**
	 * Create a new job instance.
	 *
	 * @param string|int $id
	 */
	public function __construct($id)
	{
		$this->id = $id;
	}

	/**
	 * Execute the job.
	 *
	 * @param Client $client
	 * @return void
	 * @throws \RuntimeException
	 */
	public function handle(Client $client)
	{
		try {
			$update = $client->createUpdate();
			$update->addDeleteById($this->id)
				->addCommit();

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
			throw new RuntimeException('Document could not be deleted on solr', $exception->getCode(), $exception);
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

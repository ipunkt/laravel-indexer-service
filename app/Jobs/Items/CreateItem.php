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

class CreateItem implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * Create a new job instance.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Execute the job.
	 *
	 * @param Client $client
	 * @throws \Exception
	 */
	public function handle(Client $client)
	{
		// send data to solr
		try {
			$update = $client->createUpdate();

			$doc = $update->createDocument($this->data);
			$update->addDocument($doc)
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
			throw new RuntimeException('Document could not be inserted to solr', $exception->getCode(), $exception);
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

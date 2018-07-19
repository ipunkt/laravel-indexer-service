<?php


namespace Ipunkt\LaravelIndexer\Jobs\Items;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Solarium\Client;
use Solarium\Exception\ExceptionInterface;
use Solarium\Exception\HttpException;

class DeleteByQuery implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var string
	 */
	private $query;

	/**
	 * Create a new job instance.
	 *
	 * @param string $query
	 */
	public function __construct($query)
	{
		$this->query = $query;
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
			$update->addDeleteQuery($this->query)
				->addCommit();

			$result = $client->update($update);
		} catch (HttpException $e) {
			$errorMessage = $e->getMessage();
			if (+$e->getCode() === 400) {
				$error = json_decode($e->getBody(), true);
				$errorMessage = array_get($error, 'error.msg', $e->getMessage());

				$this->job->failed($e);
				$this->job->delete();
			}
			throw new \RuntimeException($errorMessage, $e->getCode(), $e);
		} catch (ExceptionInterface $e) {
			throw new \RuntimeException('Documents for query could not be deleted on solr', $e->getCode(), $e);
		}
	}
}
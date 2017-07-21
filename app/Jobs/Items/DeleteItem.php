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
            throw new \RuntimeException('Document could not be deleted on solr', $e->getCode(), $e);
        }
    }
}

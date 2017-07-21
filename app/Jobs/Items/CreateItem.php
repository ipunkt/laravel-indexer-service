<?php

namespace Ipunkt\LaravelIndexer\Jobs\Items;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
        } catch (HttpException $e) {
            $errorMessage = $e->getMessage();
            if (+$e->getCode() === 400) {
                $error = json_decode($e->getBody(), true);
                $errorMessage = array_get($error, 'error.msg', $e->getMessage());

                $this->job->failed($e);
            }
            throw new \RuntimeException($errorMessage, $e->getCode(), $e);
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Document could not be inserted to solr', $e->getCode(), $e);
        }
    }
}

<?php

namespace Ipunkt\LaravelIndexer\Jobs\Items;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Solarium\Client;
use Solarium\Exception\ExceptionInterface;

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
        $endpoint = $client->getEndpoint();
        logger('endpoint: ' . $endpoint->getHost() . ', ' . $endpoint->getCore());
        logger('config: ' . config('solarium.endpoint.default.host') . ', ' . config('solarium.endpoint.default.core'));

        // send data to solr
        try {
            $update = $client->createUpdate();

            $doc = $update->createDocument($this->data);
            $update->addDocument($doc)
                ->addCommit();

            $result = $client->update($update);
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Document could not be inserted to solr', $e->getCode(), $e);
        }
    }
}

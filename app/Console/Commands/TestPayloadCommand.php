<?php

namespace Ipunkt\LaravelIndexer\Console\Commands;

use Illuminate\Console\Command;
use Ipunkt\LaravelIndexer\Jobs\Fake\FakeJob;
use Ipunkt\LaravelIndexer\Jobs\Items\CreateItem;
use Solarium\Client;

class TestPayloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:payload {payload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending the payload given as argument to solr';
	/**
	 * @var Client
	 */
	private $client;

	/**
	 * Create a new command instance.
	 *
	 * @param Client $client
	 */
    public function __construct(Client $client) {
        parent::__construct();
	    $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

    	$payload = $this->getPayload();

    	$fakeJob = new FakeJob;

    	$storeJob = new CreateItem( $payload );

    	$storeJob->setJob($fakeJob);
    	$storeJob->handle($this->client);

	    /**
	     * Job was released to try again - update was not successful
	     */
    	if( $fakeJob->isReleased() ) {
    		$this->error("Job was released - sending the payload was not successful");

		    return 1;
	    }

    	try {
		    $this->checkPayloadInSolr($payload);
    	} catch(\Exception $e) {
    		$this->info($e->getMessage());

		    return 2;
    	}

		return 0;
    }

	/**
	 * @return array
	 */
	private function getPayload() {
		$payloadData = $this->argument( 'payload' );

		return json_decode( $payloadData, true );
	}

	/**
	 * @param array $payload
	 * @throws \Exception
	 */
	protected function checkPayloadInSolr(array $payload) {
		$selectData = [
			'query' => 'id:'.array_get($payload, 'id')
		];

		$select = $this->client->createSelect($selectData);

		$result = $this->client->select($select);

		if($result->getNumFound() < 1)
			throw new \Exception('No document with the given id found.');
		if($result->getNumFound() > 1)
			throw new \Exception('More than one document with the given id found.');

		$document = $result[0];

		foreach ($payload as $key => $value) {
			$solrValue = $document->${$key};
			if( $solrValue != $value)
				throw new \Exception("Field $key was not updated correctly: $$solrValue. Expected: $value");
		}
	}
}

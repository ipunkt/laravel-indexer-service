<?php

namespace Ipunkt\LaravelIndexer\Console\Commands;

use Illuminate\Console\Command;
use Ipunkt\LaravelIndexer\Jobs\Fake\FakeJob;
use Ipunkt\LaravelIndexer\Jobs\Items\CreateItem;
use Solarium\Client;

/**
 * Class TestPayloadCommand
 * @package Ipunkt\LaravelIndexer\Console\Commands
 *
 * This command allows sending json data given as argument synchronously.
 * It tests that the solr returns a document with the same id as the payload.
 * If expected-fields are given as comma seperated list then the fields listed there are compared between the payload
 *  and the document returned by solr.
 */
class TestPayloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:payload {payload} {expected-fields?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending the payload given as argument to solr. If a comma separated list of expected fields is given the response will be compared to the payload for those fields.';

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

	    $expectedFields = $this->getExpectedFields();

    	try {
		    $this->checkPayloadInSolr($payload, $expectedFields);
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
	 * @return array
	 */
	private function getExpectedFields() {
		$payloadData = $this->argument( 'expected-fields' );

		return explode(',', $payloadData);
	}

	/**
	 * @param array $payload
	 * @param array $expectedFields
	 * @throws \Exception
	 */
	protected function checkPayloadInSolr(array $payload, array $expectedFields) {
		$selectData = [
			'query' => 'id:'.array_get($payload, 'id')
		];

		$select = $this->client->createSelect($selectData);

		$result = $this->client->select($select);

		if($result->getNumFound() < 1)
			throw new \Exception('No document with the given id found.');
		if($result->getNumFound() > 1)
			throw new \Exception('More than one document with the given id found.');

		if( empty($expectedFields) )
			return;

		$array = $result->getIterator();
		$document = $array->current();
		$solrFields = $document->getFields();

		foreach ($expectedFields as $key) {
			if( !array_key_exists($key, $solrFields ) )
				throw new \Exception("Field $key does not exist in solr.");

			$payloadValue = array_get($payload, $key);
			$solrValue = array_get( $solrFields, $key);
			if( is_array($solrValue) && !is_array($payloadValue) )
				$solrValue = $solrValue[0];

			if( is_array($solrValue) && is_array($payloadValue) ) {
				sort($payloadValue);
				$payloadValue = implode(',', $payloadValue);
				sort($solrValue);
				$solrValue = implode(',', $solrValue);
			}


			if( $solrValue != $payloadValue )
				throw new \Exception("Field $key was not updated correctly: $$solrValue. Expected: $payloadValue");
		}
	}
}

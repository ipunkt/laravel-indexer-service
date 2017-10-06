<?php

namespace Ipunkt\LaravelIndexer\Jobs\Fake;

/**
 * Class FakeJob
 *
 * This is NOT a Queue Job. Do NOT add it to the queue.
 * It is used to retrieve successs information in the test:payload command.
 */
class FakeJob implements \Illuminate\Contracts\Queue\Job {
	/**
	 * @var bool
	 */
	private $released = false;

	/**
	 * @var int
	 */
	private $releaseDelay = 0;

	/**
	 * @var bool
	 */
	private $deleted = false;

	/**
	 * Fire the job.
	 *
	 * @return void
	 */
	public function fire() {
	}

	/**
	 * Release the job back into the queue.
	 *
	 * @param  int $delay
	 * @return mixed
	 */
	public function release( $delay = 0 ) {
		$this->released = true;
		$this->releaseDelay = $delay;
	}

	/**
	 * Delete the job from the queue.
	 *
	 * @return void
	 */
	public function delete() {
		$this->deleted = true;
	}

	/**
	 * Determine if the job has been deleted.
	 *
	 * @return bool
	 */
	public function isDeleted() {
		return $this->deleted;
	}

	/**
	 * Determine if the job has been deleted or released.
	 *
	 * @return bool
	 */
	public function isDeletedOrReleased() {
		return $this->released || $this->released;
	}

	/**
	 * Get the number of times the job has been attempted.
	 *
	 * @return int
	 */
	public function attempts() {
		return 0;
	}

	/**
	 * Process an exception that caused the job to fail.
	 *
	 * @param  \Throwable $e
	 * @return void
	 */
	public function failed( $e ) {
	}

	/**
	 * The number of times to attempt a job.
	 *
	 * @return int|null
	 */
	public function maxTries() {
		return 1;
	}

	/**
	 * The number of seconds the job can run.
	 *
	 * @return int|null
	 */
	public function timeout() {
		return null;
	}

	/**
	 * Get the name of the queued job class.
	 *
	 * @return string
	 */
	public function getName() {
		return null;
	}

	/**
	 * Get the resolved name of the queued job class.
	 *
	 * Resolves the name of "wrapped" jobs such as class-based handlers.
	 *
	 * @return string
	 */
	public function resolveName() {
		return null;
	}

	/**
	 * Get the name of the connection the job belongs to.
	 *
	 * @return string
	 */
	public function getConnectionName() {
		return null;
	}

	/**
	 * Get the name of the queue the job belongs to.
	 *
	 * @return string
	 */
	public function getQueue() {
		return null;
	}

	/**
	 * Get the raw body string for the job.
	 *
	 * @return string
	 */
	public function getRawBody() {
		return null;
	}

	/**
	 * @return bool
	 */
	public function isReleased(): bool {
		return $this->released;
	}
}
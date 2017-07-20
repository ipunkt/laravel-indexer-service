<?php

namespace Ipunkt\LaravelIndexer\Http\Api\Items;

use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Ipunkt\LaravelIndexer\EnvironmentValidation;
use Ipunkt\LaravelIndexer\Jobs\Items\CreateItem;
use Ipunkt\LaravelIndexer\Jobs\Items\DeleteItem;
use Ipunkt\LaravelIndexer\Jobs\Solr\Optimize;
use Ipunkt\LaravelJsonApi\Contracts\RequestHandlers\HandlesDeleteRequest;
use Ipunkt\LaravelJsonApi\Contracts\RequestHandlers\HandlesPostRequest;
use Ipunkt\LaravelJsonApi\Http\RequestHandlers\RequestHandler;
use Ipunkt\LaravelJsonApi\Http\Requests\ApiRequest;
use Tobscure\JsonApi\Parameters;

class ItemsRequestHandler extends RequestHandler implements HandlesPostRequest, HandlesDeleteRequest
{
    /**
     * @var EnvironmentValidation
     */
    private $environmentValidation;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * ItemsRequestHandler constructor.
     * @param EnvironmentValidation $environmentValidation
     * @param Repository $cache
     */
    public function __construct(EnvironmentValidation $environmentValidation, Repository $cache)
    {
        $this->environmentValidation = $environmentValidation;
        $this->cache = $cache;
    }

    /**
     * handles post request
     *
     * @param \Ipunkt\LaravelJsonApi\Http\Requests\ApiRequest $request
     * @param Parameters $parameters
     * @return Model|mixed|null
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(ApiRequest $request, Parameters $parameters)
    {
        $requestModel = $request->asRequestModel();

        //  prepare data
        $attributes = $requestModel->attributes();
        if (!isset($attributes['id']) && $requestModel->id() !== null) {
            $attributes['id'] = $requestModel->id();
        }

        //  validate data
        $this->environmentValidation->validate($attributes);

        dispatch(new CreateItem($attributes));

        $this->prepareOptimize();

        return null;
    }

    /**
     * handles delete request
     *
     * @param string|int $id
     * @param \Ipunkt\LaravelJsonApi\Http\Requests\ApiRequest $request
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete($id, ApiRequest $request)
    {
        $this->environmentValidation->validateId($id);

        dispatch(new DeleteItem($id));

        $this->prepareOptimize();

        return null;
    }

    /**
     * prepares optimize call to solr
     */
    private function prepareOptimize()
    {
        $cacheKey = 'NEXT_OPTIMIZE_ON_SOLR';

        /** @var Carbon $nextOptimizeOnSolr */
        $nextOptimizeOnSolr = $this->cache->get($cacheKey, Carbon::now());

        // if cached value is too old, make it now
        if ($nextOptimizeOnSolr->isPast()) {
            $nextOptimizeOnSolr = Carbon::now();
        }

        //  adding 2 minutes for next possible optimize
        $nextOptimizeOnSolr->addMinutes(2);

        //  store until next optimize is possible
        $this->cache->put($cacheKey, $nextOptimizeOnSolr, $nextOptimizeOnSolr);

        $job = (new Optimize($cacheKey))
            ->delay($nextOptimizeOnSolr);

        dispatch($job);
    }
}
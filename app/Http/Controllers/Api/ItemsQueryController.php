<?php

namespace Ipunkt\LaravelIndexer\Http\Controllers\Api;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ipunkt\LaravelIndexer\Http\Controllers\Controller;
use Ipunkt\LaravelJsonApi\Contracts\RequestHandlers\ApiRequestHandler;
use Solarium\Client;
use Solarium\QueryType\Select\Result\Document;

class ItemsQueryController extends Controller
{
    use ValidatesRequests;

    /**
     * query input rules
     * @see http://solarium.readthedocs.io/en/stable/queries/select-query/building-a-select-query/building-a-select-query/
     *
     * @var array
     */
    private $selectQueryRules = [
        'query' => 'required',
        'start' => 'sometimes|numeric|min:0',
        'rows' => 'sometimes|numeric|min:0',
        'fields' => 'sometimes|array',
        'sort' => 'sometimes|array',
        'filterquery' => 'sometimes|array',
        'component' => 'sometimes|array',
    ];

    /**
     * executing a select query
     *
     * @param Client $client
     * @param Request $request
     * @return JsonResponse
     */
    public function select(Client $client, Request $request)
    {
        $this->validate($request, $this->selectQueryRules);
        $query = $client->createSelect($request->all());
        $resultset = $client->select($query);

        $documentsCount = $resultset->getNumFound();
        $resultCount = $resultset->count();
        $maxScore = $resultset->getMaxScore();

        $page = $documentsCount > 0
            ? ceil($request->get('start', 0) / $documentsCount) + 1
            : 1;

        $items = collect();

        if ($resultCount > 0) {
            /** @var Document $document */
            foreach ($resultset as $document) {
                $items->push($document->getFields());
            }
        }

        return response()->json([
            'data' => $items->toArray(),
            'meta' => [
                'pagination' => [
                    'start' => +$request->get('start', 0),
                    'rows' => min($resultCount, $request->get('rows', $resultCount)),
                    'total' => $documentsCount,
                    'page' => $page,
                ],
                'result' => [
                    'max-score' => $maxScore,
                ]
            ],
        ], Response::HTTP_OK)
            ->header('Content-Type', ApiRequestHandler::CONTENT_TYPE);
    }
}

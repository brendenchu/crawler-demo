<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CrawlerResource;
use App\Services\Crawler;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrawlerBackEndController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'limit' => 'required',
        ]);

        // If validation fails, return a 400 response with the validation errors.
        if ($validator->fails()) {
            return response()->json([
                'message' => 'There was an error validating the input.',
                'errors' => $validator->errors()
            ], 400);
        }

        // Create a new crawler instance or return server error if unable to create instance.
        if (!($crawler = (new Crawler($request->input('url'), $request->input('limit')))->crawl())) {
            return response()->json([
                'message' => 'Could not crawl URL',
            ], 500);
        }


        return response()->json([
            'message' => 'Crawling was successful.',
            'data' => CrawlerResource::make($crawler->toArray()),
        ]);
    }
}

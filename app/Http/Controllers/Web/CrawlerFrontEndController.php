<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class CrawlerFrontEndController extends Controller
{
    public function __invoke()
    {
        return view('crawler.frontend', [
            'title' => 'Crawler',

        ]);
    }
}

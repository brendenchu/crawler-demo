<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class CrawlerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        $host = parse_url($this['originUrl'], PHP_URL_HOST);
        $crawledPages = collect($this['crawledPages']);
        $totalInternalLinks = collect($this['uncrawledPages'])->filter(fn($url) => (!str_starts_with($url, 'http') || strpos($url, $host)))->count();

        return [
            'originUrl' => urldecode($this['originUrl']),
            'totalPagesCrawled' => count($this['crawledPages']),
            'totalUniqueImages' => count($this['crawledImages']),
            'totalInternalLinks' => $totalInternalLinks,
            'totalExternalLinks' => count($this['uncrawledPages']) - $totalInternalLinks,
            'avgPageLoad' => round($crawledPages->pluck('time')->avg(), 2),
            'avgWordCount' => round($crawledPages->pluck('wordCount')->avg(), 2),
            'avgTitleLength' => round($crawledPages->pluck('title')->map(fn($title) => strlen($title))->avg(), 2),
            'crawledPages' => $this['crawledPages'],
        ];
    }
}

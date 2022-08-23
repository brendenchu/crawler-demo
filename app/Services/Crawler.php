<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Crawler
{
    private string $originUrl;
    private array $crawledPages = [];
    private array $uncrawledPages = [];
    private array $crawledImages = [];


    public function __construct(
        public string $url,
        public int    $limit = 1
    )
    {
        // Set the origin URL, if null.
        $this->originUrl ??= $url;
    }

    /**
     * It crawls a given URL, and recursively crawls the links on that page, until the given limit is reached
     *
     * @return Crawler
     * @throws Exception
     */
    public function crawl(): Crawler
    {
        while ($this->limit > 0 && !in_array($this->url, $this->crawledPages, true)) {

            // Get the URL parts
            $url_parts = parse_url($this->url);

            // Get time of page load start
            $pageLoadStart = microtime(true);

            // Get the HTTP response for the given URL.
            if (!($response = Http::get($this->url))) {
                throw new RuntimeException('Could not get HTML from URL');
            }

            // Get time of page load end
            $pageLoadEnd = microtime(true);

            // Get the HTML from the response.
            $html = $response->body();

            // Get the title of the page.
            preg_match('/<title>(.*)<\/title>/', $html, $titleMatches);

            // Add the URL to the crawled URLs array.
            $this->crawledPages[] = collect([
                'url' => $this->url,
                'title' => $titleMatches[1] ?? '',
                'status' => $response->status(),
                'time' => $pageLoadEnd - $pageLoadStart ?? 0.0,
                'wordCount' => count(explode(' ', strip_tags($html))) ?: 0,
            ]);

            // Get number of unique image URLs in the HTML.
            preg_match_all('/<img[^>]+src="([^">]+)"/i', $html, $matches);
            $imageUrls = $matches[1] ?? [];
            $this->crawledImages = array_unique(array_merge($this->crawledImages, $imageUrls));

            // Get hrefs for all links in the HTML.
            preg_match_all('/<a[^>]+href="([^">]+)"/i', $html, $matches);
            $uncrawled = isset($matches[1]) ? collect($matches[1])->filter(fn($url) => !preg_match('/^(mailto:|tel:|#)/i', $url))->transform(fn($url) => !str_starts_with($url, 'http') ? $url_parts['scheme'] . '://' . $url_parts['host'] . $url : $url)->toArray() : [];
            $this->uncrawledPages = array_unique(array_merge($this->uncrawledPages, $uncrawled));

            // Determine new URL to crawl, and decrement limit.
            $this->url = $this->uncrawledPages[array_rand($this->uncrawledPages)];
            $this->limit--;
        }
        // Return the Crawler instance.
        return $this;

    }

    /**
     * Return the Crawler private properties as an array.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'originUrl' => $this->originUrl,
            'crawledPages' => $this->crawledPages,
            'uncrawledPages' => $this->uncrawledPages,
            'crawledImages' => $this->crawledImages,
        ];
    }

}

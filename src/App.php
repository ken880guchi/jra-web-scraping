<?php

namespace Ken880guchi\Jra;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class App
{
    const TOP_URL = 'http://www.jra.go.jp/';

    /** @var \Goutte\Client $client */
    public $client = null;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string $jsCode
     * @return mixed
     */
    public function getShowPageStringFromJsCode($jsCode)
    {
        $explodedJsCodeArray = explode(',', $jsCode);
        preg_match("/'.*'/", $explodedJsCodeArray[1], $match);

        return str_replace("'", '', $match[0]);
    }

    /**
     * @param string $targetClass
     * @param string $url
     * @param array  $showPageStrings
     * @return array
     */
    public function getShowPageStrings($targetClass, $url, $showPageStrings = [])
    {
        if (empty($showPageStrings)) {
            return $this->extractShowPageString($targetClass, $url);
        }

        $extractShowPageStrings = [];

        foreach ($showPageStrings as $showPageString) {
            $tmp = $this->extractShowPageString($targetClass, $url, $showPageString);
            $extractShowPageStrings = array_merge($extractShowPageStrings, $tmp);
        }

        return $extractShowPageStrings;
    }

    /**
     * @param string $targetClass
     * @param string $url
     * @param string $showPageString
     * @return array
     */
    private function extractShowPageString($targetClass, $url, $showPageString = '')
    {
        $extractShowPageStrings = [];

        $crawler = $this->client->request('POST', $url, ['cname' => $showPageString]);
        $crawler->filter($targetClass)->each(function (Crawler $node) use (&$extractShowPageStrings) {
            $jsCode = $node->attr('onclick');
            $extractShowPageStrings[] = $this->getShowPageStringFromJsCode($jsCode);
        });

        return $extractShowPageStrings;
    }
}

<?php

require 'vendor/autoload.php'; // Include GuzzleHttp

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Crawler{
    
    public $client;
    public $fileName;
    public $url;
    public $topicsType = '';
    public $topicsurl = [];
    
    public function __construct(Type $var = null) {
        $this->client = new Client();
    }

    public function setUrl($url = NULL){
        $this->url = $this->makeAbsoluteUrl($url);
    }

    public function setTopicsTitle($topicTitle){
        $this->topicsType = $topicTitle;
    }

    private function makeAbsoluteUrl($relativeUrl) {
        // Check if the URL is already absolute
        if (parse_url($relativeUrl, PHP_URL_SCHEME) !== null) {
            return $relativeUrl;
        }
        // Combine the base URL with the relative URL
        $domain = parse_url($this->url, PHP_URL_SCHEME) . '://' . parse_url($this->url, PHP_URL_HOST);
        return rtrim($domain, '/') . '/' . ltrim($relativeUrl, './');
    }

    public function fetchContent(){
        try {
            $response = $this->client->request('GET', $this->url);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }
    }

    private function domLoad($content){
        $dom = new DOMDocument();
        @$dom->loadHTML($content);
        return $dom;
    }

    public function fetchUrl($content, $anchorText){
        $dom = $this->domLoad($content);
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $anchor) {
            if ($anchor->nodeValue == $anchorText) {
                return $anchor->getAttribute('href');
            }
        }
    }

    public function fetchTopicsUrl($content, $pageCount = 1){
        $dom = $this->domLoad($content);
        $xpath = new DOMXPath($dom);

        // Find all <a> elements within the specified xpath
        $links = $xpath->query('//div[contains(@class, "search-results-section-body")]//h3[contains(@class, "result-title")]//a');
        
        foreach ($links as $link) {
            $this->topicsurl[] = $link->getAttribute('href');
        }

        //Check if next page is present
        $pageCountNew = ($pageCount + 1);
        $nextLink = $xpath->query('//ul[@class="pagination-page-list"]//a[contains(text(), "'.$pageCountNew.'")]');

        if ($nextLink->length > 0) {
            $this->setUrl($nextLink->item(0)->getAttribute('href'));
            $this->fetchTopicsUrl($this->fetchContent(), $pageCountNew);
        }
        return true;
    }

    public function fetchTopicsDetails(){
        //Check if Topics present
        if(!empty($this->topicsurl)){
            $allReviews = [];
            foreach($this->topicsurl as $topicSpecUrl){
                $topicAllData = [];
                
                $this->setUrl($topicSpecUrl);
                
                // Add topics URL
                $topicAllData[] = $this->url;

                // Add topics Type
                $topicAllData[] = $this->topicsType;

                $topicContent = $this->fetchContent();
                $dom = $this->domLoad($content);
                $xpath = new DOMXPath($dom);

                // Find title
                $titlexPath = $xpath->query('//header[@class="publication-header"]//h1[@class="publication-title"]');
                $topicTitle = '';
                if ($titlexPath->length > 0) {
                    $topicTitle = $titlexPath->item(0)->nodeValue;
                }

                // Add topics Title
                $topicAllData[] = $topicTitle;


                // Find all author names
                $authorLinks = $xpath->query('//div[@class="publication-authors"]//ul[@class="authors"]/li[@class="author"]/a');
                $authorNames = [];
                foreach ($authorLinks as $authorLink) {
                    $authorNames[] = $authorLink->nodeValue;
                }

                // Add topics Authors
                $topicAllData[] = implode(', ', $authorNames);

                
                // Find  publish Date
                $dateXpath = $xpath->query('//div[@class="publication-metadata-block"]//span[@class="publish-date"]');
                $topicDate = '';
                if ($dateXpath->length > 0) {
                    $topicDate = $dateXpath->item(0)->nodeValue;
                }

                // Add topics Publish Date
                $topicAllData[] = $topicDate;

                // Add into Reviews
                $allReviews[] = implode('|', $topicAllData);
            }

            if(!empty($allReviews)){
                // Write content to file with each piece of information delimited by new lines
                $writeContent = implode("\n", $allReviews)."\n";

                // Check if the file exists
                if (!file_exists($this->fileName)) {
                    $file = fopen($this->fileName, "w");
                    fwrite($file, $writeContent);
                    fclose($file);
                } else {
                    // Open the file in append mode to append content
                    $file = fopen($this->fileName, "a");
                    // Write content to the file
                    fwrite($file, $writeContent);
                    fclose($file);
                }
            }
        }
        return true;
    }

    public function removeFile($filename){
        $this->fileName = $filename;
        if (file_exists($this->fileName)) {
            unlink($this->fileName);
        }
    }
}

// Start crawling from a specific URL
$newCrawler = new Crawler();
$newCrawler->removeFile("output.txt");
$newCrawler->setUrl("https://www.cochranelibrary.com");
$topicsSearch = ["Allergy & intolerance"];
foreach($topicsSearch as $topicsType){
    $newCrawler->setTopicsTitle($topicsType);
    $newCrawler->setUrl($newCrawler->fetchUrl($newCrawler->fetchContent(), "Browse"));
    $newCrawler->setUrl($newCrawler->fetchUrl($newCrawler->fetchContent(), $topicsType));
    $newCrawler->fetchTopicsUrl($newCrawler->fetchContent());
    $newCrawler->fetchTopicsDetails();
}
print "Crawler Successfully Completed";
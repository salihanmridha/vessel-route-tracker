<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

trait Scrapper
{
    private static $scrapingBeeUrl = "https://app.scrapingbee.com/api/v1/";


    /**
     * @param $url
     * @return false|JsonResponse|string
     */
    public function phpScrapper($url)
    {
        $context = stream_context_create(
            array(
                "http" => array(
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                )
            )
        );
        $html = null;
        try {
            $html = file_get_contents($url, false, $context);
        } catch (\Exception $e) {
            $html = response()->json([
                "status" => Response::HTTP_NOT_FOUND,
                "success" => false,
                "message" => "The IMO Code that you entered does not exist in our database. Please try again.",
                "data" => [],
            ]);
        }

        return $html;


    }

    /**
     * @param $url
     * @return array|JsonResponse
     */
    public function getVesselInfo($url)
    {
        $html = $this->phpScrapper($url);

        if($html instanceof JsonResponse || $html == null){
            return $html;
        }

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_use_internal_errors(false);

        $xpath = new \DOMXPath($doc);
        $table = $xpath->query('//table[@class="tparams"]')->item(0);
        $rows = $table->getElementsByTagName('tr');

        $result = array();

        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            $key = trim($cells->item(0)->nodeValue);
            $key = strtolower(preg_replace('/[^a-zA-Z0-9.]/', '', $key));
            $value = trim($cells->item(1)->nodeValue);
            $result[$key] = $value;
        }

        return $result;
    }
}

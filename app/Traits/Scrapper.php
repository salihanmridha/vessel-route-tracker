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

    /**
     * @param $url
     * @return array|false|JsonResponse|string
     */
    public function getVesselRoutes($url)
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
        $table = $xpath->query('//table[@class="aparams"]')->item(0);
        $rows = $table->getElementsByTagName('tr');



        $result = array();

        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            $key = trim($cells->item(0)->nodeValue);
            $key = strtolower(preg_replace('/[^a-zA-Z0-9.]/', '', $key));
            $value = trim($cells->item(1)->nodeValue);
            $result[$key] = $value;
        }

        $departurePortData = $this->getDeparturePortData($xpath);
        $arrivalPortData = $this->getArrivalPortData($xpath);

        $result["departureport"] = $departurePortData["port"];
        $result["departureatd"] = $departurePortData["atd"];

        $result["arrivalport"] = $arrivalPortData["port"];
        $result["arrivalatd"] = $arrivalPortData["atd"];

        return $result;
    }

    private function getDeparturePortData($xpath)
    {
        // select the parent div based on its class
        $parent_div = $xpath->query("//div[contains(@class, 'vi__r1') and contains(@class, 'vi__stp')]")->item(0);

        $child_elements = $xpath->query("./*", $parent_div);

        $content_3_Yih = '';
        $content_value = '';

        foreach ($child_elements as $child_element) {
            // check the tag name of the child element
            if ($child_element->tagName == 'div' && strpos($child_element->getAttribute('class'), '_3-Yih') !== false) {
                $content_3_Yih = $child_element->textContent;
            } elseif ($child_element->tagName == 'div' && strpos($child_element->getAttribute('class'), '_value') !== false) {
                $content_value = $child_element->textContent;
            } elseif ($child_element->tagName == 'a') {
                $content_3_Yih = $child_element->textContent;
            }
        }

        return [
            "port" => $content_3_Yih,
            "atd" => $content_value,
        ];
    }

    private function getArrivalPortData($xpath)
    {
        // select the parent div based on its class
        $parent_div = $xpath->query("//div[contains(@class, 'vi__r1') and contains(@class, 'vi__sbt')]")->item(0);

        $child_elements = $xpath->query("./*", $parent_div);

        $content_3_Yih = '';
        $content_value = '';

        foreach ($child_elements as $child_element) {
            // check the tag name of the child element
            if ($child_element->tagName == 'div' && strpos($child_element->getAttribute('class'), '_3-Yih') !== false) {
                $content_3_Yih = $child_element->textContent;
            } elseif ($child_element->tagName == 'a') {
                $content_3_Yih = $child_element->textContent;
            } elseif ($child_element->tagName == 'div' && strpos($child_element->getAttribute('class'), '_value') !== false) {
                // get the text content of the span element with class _mcol12
                $timestamp_span = $child_element->getElementsByTagName('span')[0];
                $timestamp_text = $timestamp_span->textContent;
                $content_value = $timestamp_text;
            }

            // stop processing if both content variables have been filled
            if ($content_3_Yih != '' && $content_value != '') {
                break;
            }
        }

        return [
            "port" => $content_3_Yih,
            "atd" => $content_value,
        ];
    }
}

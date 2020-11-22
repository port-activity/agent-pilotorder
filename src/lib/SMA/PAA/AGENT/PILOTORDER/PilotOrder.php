<?php
namespace SMA\PAA\AGENT\PILOTORDER;

use SMA\PAA\CURL\ICurlRequest;
use SMA\PAA\RESULTPOSTER\IResultPoster;
use SMA\PAA\AGENT\ApiConfig;
use SMA\PAA\AINO\AinoClient;

use Exception;
use SMA\PAA\CURL\CurlRequest;
use SMA\PAA\RESULTPOSTER\ResultPoster;

class PilotOrder
{
    private $config;
    private $serviceUrl;
    private $curlRequest;
    private $resultPoster;
    private $aino;

    public function __construct(
        ICurlRequest $curlRequest = null,
        IResultPoster $resultPoster = null,
        string $locode,
        AinoClient $aino = null
    ) {
        $this->curlRequest = $curlRequest ?: new CurlRequest();
        $this->resultPoster = $resultPoster ?: new ResultPoster(new CurlRequest());
        $this->locode = $locode;
        $this->aino = $aino;
        if (!$this->locode) {
            throw new Exception("Missing locode");
        }
        $this->config = require("PilotOrderConfig.php");
        date_default_timezone_set("UTC");
        $this->serviceUrl = $this->config["serviceurl"] . $this->locode;
    }

    public function execute(ApiConfig $apiConfig)
    {
        return $this->postResults(
            $apiConfig,
            $this->parseResults(
                $this->fetchResults(),
            )
        );
    }

    private function fetchResults(): array
    {
        echo "Initing url: " . $this->serviceUrl . "\n";
        $this->curlRequest->init($this->serviceUrl);
        $this->curlRequest->setOption(CURLOPT_ENCODING, ""); // allow all encodings, gzip etc.
        $this->curlRequest->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curlRequest->setOption(
            CURLOPT_HTTPHEADER,
            array(
                'Authorization: Basic ' . getenv("PILOTORDER_BASIC_AUTH")
            )
        );
        $curlResponse = $this->curlRequest->execute();

        if ($curlResponse === false) {
            $info = $this->curlRequest->getInfo();
            $this->curlRequest->close();
            throw new Exception("Error occured during curl exec.\ncurl_getinfo returns:\n".print_r($info, true)."\n");
        }

        $this->curlRequest->close();
        $decoded = json_decode($curlResponse, true);

        if (isset($decoded["error"])) {
            throw new Exception("Error response from server ".$this->serviceUrl.":\n".print_r($decoded, true)."\n");
        }

        if (!isset($decoded["pilotages"])) {
            throw new Exception("Failed to get pilotages. Raw result was: " . $curlResponse);
        }

        return $decoded["pilotages"];
    }

    private function parseResults(array $rawResults): array
    {
        $tools = new PilotOrderTools();
        return array_filter(
            array_map(function ($result) use ($tools) {
                return $tools->convert($result, $this->locode);
            }, $rawResults),
            function ($data) {
                return $data != null;
            }
        );
    }

    private function postResults(ApiConfig $apiConfig, array $results)
    {
        $countOk = 0;
        $countFailed = 0;

        $ainoTimestamp = gmdate("Y-m-d\TH:i:s\Z");

        echo "Posting " . sizeof($results) . " results to api.\n";
        foreach ($results as $result) {
            echo json_encode($result) . "\n";

            $ainoFlowId = $this->resultPoster->resultChecksum($apiConfig, $result);
            try {
                $this->resultPoster->postResult($apiConfig, $result);
                ++$countOk;
                if (isset($this->aino)) {
                    $this->aino->succeeded(
                        $ainoTimestamp,
                        "PilotOrder agent succeeded",
                        "Post",
                        "timestamp",
                        ["imo" => $result["imo"]],
                        [],
                        $ainoFlowId
                    );
                }
            } catch (\Exception $e) {
                ++$countFailed;
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
                if (isset($this->aino)) {
                    $this->aino->failure(
                        $ainoTimestamp,
                        "PilotOrder agent failed",
                        "Post",
                        "timestamp",
                        [],
                        [],
                        $ainoFlowId
                    );
                }
            }
        }
        echo "Posting done.\n";

        return [
            "ok" => $countOk,
            "failed" => $countFailed
        ];
    }
}

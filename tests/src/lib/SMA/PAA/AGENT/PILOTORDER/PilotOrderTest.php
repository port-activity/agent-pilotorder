<?php
namespace SMA\PAA\AGENT\PILOTORDER;

use PHPUnit\Framework\TestCase;

use SMA\PAA\FAKECURL\FakeCurlRequest;
use SMA\PAA\FAKERESULTPOSTER\FakeResultPoster;
use SMA\PAA\AGENT\ApiConfig;

final class PilotOrderTest extends TestCase
{
    public function testExecute(): void
    {
        $curlRequest = new FakeCurlRequest();
        $resultPoster = new FakeResultPoster();
        $pilotOrder = new PilotOrder($curlRequest, $resultPoster, "FIRAU");
        $curlRequest->executeReturn = file_get_contents(__DIR__ . "/ValidServerData.json");
        $pilotOrder->execute(
            new ApiConfig("key", "http://url/foo", ["foo"]),
        );
        // file_put_contents(__DIR__ . "/ValidPosterData.json", json_encode($resultPoster->results, JSON_PRETTY_PRINT));
        $this->assertEquals(
            $resultPoster->results,
            json_decode(file_get_contents(__DIR__ . "/ValidPosterData.json"), true)
        );
    }
}

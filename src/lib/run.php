<?php
namespace SMA\PAA\AGENT;

require_once __DIR__ . "/../../vendor/autoload.php";
require_once "init.php";

use SMA\PAA\AGENT\PILOTORDER\PilotOrder;
use SMA\PAA\AINO\AinoClient;
use Exception;

$apiKey = getenv("API_KEY");
$apiUrl = getenv("API_URL");
$locode = getenv("LOCODE");
$ainoKey = getenv("AINO_API_KEY");

$apiParameters = ["imo", "vessel_name", "time_type", "state", "time", "payload"];

$apiConfig = new ApiConfig($apiKey, $apiUrl, $apiParameters);

echo "Starting job.\n";

$aino = null;
if ($ainoKey) {
    $toApplication = parse_url($apiUrl, PHP_URL_HOST);
    $aino = new AinoClient($ainoKey, "PilotOrder", $toApplication);
}
$agent = new PilotOrder(null, null, $locode, $aino);

$aino = null;
if ($ainoKey) {
    $aino = new AinoClient($ainoKey, "PilotOrder service", "PilotOrder");
}
$ainoTimestamp = gmdate("Y-m-d\TH:i:s\Z");

try {
    $counts = $agent->execute($apiConfig);
    if (isset($aino)) {
        $aino->succeeded($ainoTimestamp, "PilotOrder agent succeeded", "Batch run", "timestamp", [], $counts);
    }
} catch (\Exception $e) {
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
    if (isset($aino)) {
        $aino->failure($ainoTimestamp, "PilotOrder agent failed", "Batch run", "timestamp", [], []);
    }
}

echo "All done.\n";

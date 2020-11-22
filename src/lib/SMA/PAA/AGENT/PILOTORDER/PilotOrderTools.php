<?php
namespace SMA\PAA\AGENT\PILOTORDER;

class PilotOrderTools
{
    const STATUS_ESTIMATE   = "ESTIMATE";
    const STATUS_NOTICE     = "NOTICE";
    const STATUS_ORDER      = "ORDER";
    const STATUS_ACTIVE     = "ACTIVE";
    const STATUS_FINISHED   = "FINISHED";
    // Note: this is newer status, earlier we got also cancelled status as "FINISHED"
    const STATUS_CANCELLED  = "CANCELLED";

    public function convert(array $data, string $inboundLocode)
    {
        $stateMap = [
            self::STATUS_ESTIMATE   => "Pilotage_Commenced",
            self::STATUS_NOTICE     => "Pilotage_Commenced",
            self::STATUS_ORDER      => "Pilotage_Commenced",
            self::STATUS_ACTIVE     => "Pilotage_Commenced",
            self::STATUS_FINISHED   => "Pilotage_Completed"
        ];

        $timeMap = [
            self::STATUS_ESTIMATE   => "startTime",
            self::STATUS_NOTICE     => "startTime",
            self::STATUS_ORDER      => "startTime",
            self::STATUS_ACTIVE     => "startTime",
            self::STATUS_FINISHED   => "endTime"
        ];

        // note: notice (="ennakko") replaces estimate (="arvio") on pilotweb
        $typeMap = [
            self::STATUS_ESTIMATE   => "Estimated",
            self::STATUS_NOTICE     => "Estimated",
            self::STATUS_ORDER      => "Planned",
            self::STATUS_ACTIVE     => "Actual",
            self::STATUS_FINISHED   => "Actual"
        ];
        if (isset($stateMap[$data["state"]])) {
            $payload = [
                "fromPoint"         => $data["startCode"],
                "toPoint"           => $data["endCode"],
                "direction"         => $data["endCode"] === $inboundLocode ? "inbound" : "outbound",
                "original_message"  => $data
            ];

            // for berths either either start berth (for outbound) or end berth (inbound) is set
            if (!empty($data["startBerth"]["name"])) {
                $payload["berth_name"] = $data["startBerth"]["name"];
            }

            if (!empty($data["endBerth"]["name"])) {
                $payload["berth_name"] = $data["endBerth"]["name"];
            }

            $data = [
                "imo"           => $data["vesselImo"] ?: 0,
                "vessel_name"   => $data["vesselName"],
                "time_type"     => $typeMap[$data["state"]],
                "state"         => $stateMap[$data["state"]],
                "time"          => $data[$timeMap[$data["state"]]],
                "payload"       => $payload
            ];

            return $data;
        }
        return [];
    }
}

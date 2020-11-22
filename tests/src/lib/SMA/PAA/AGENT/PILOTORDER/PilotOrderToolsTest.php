<?php

namespace SMA\PAA\AGENT\PILOTORDER;

use PHPUnit\Framework\TestCase;

use SMA\PAA\FAKECURL\FakeCurlRequest;
use SMA\PAA\FAKERESULTPOSTER\FakeResultPoster;
use SMA\PAA\AGENT\ApiConfig;

final class PilotOrderToolsTest extends TestCase
{
    /*
        01:35:00 01:00:00 Estimated:Pilotage_Commenced            Pilot inbound ordered
        01:40:00 01:00:00 Planned:Pilotage_Commenced              Pilotage inbound started
        01:45:00 01:00:00 Actual:Pilotage_Commenced               Pilot - Destination
        02:00:00 01:00:00 Actual:Pilotage_Completed               Pilotage finished
        02:25:00 01:00:00 Estimated:Pilotage_Commenced            Pilot outbound ordered
        02:30:00 01:00:00 Actual:Pilotage_Commenced               Pilotage outbound started
        02:40:00 01:00:00 Actual:Pilotage_Completed               Pilotage outbound finished
    */
    public function testInboundActualPilotageRequestedParsing(): void
    {
        $json = '{
            "id": 456326,
            "vesselImo": 9387425,
            "vesselName": "Empire",
            "startTime": "2019-12-12T21:31:00Z",
            "endTime": "2019-12-12T22:34:00Z",
            "startCode": "FIRAE",
            "endCode": "FIRAU",
            "state": "ESTIMATE"
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
                "imo"           => 9387425,
                "vessel_name"   => "Empire",
                "time_type"     => "Estimated",
                "state"         => "Pilotage_Commenced",
                "time"          => "2019-12-12T21:31:00Z",
                "payload"       =>  [
                    "fromPoint" => "FIRAE",
                    "toPoint"   => "FIRAU",
                    "direction" => "inbound",
                    "original_message" => [
                        'id' => 456326,
                        'vesselImo' => 9387425,
                        'vesselName' => 'Empire',
                        'startTime' => '2019-12-12T21:31:00Z',
                        'endTime' => '2019-12-12T22:34:00Z',
                        'startCode' => 'FIRAE',
                        'endCode' => 'FIRAU',
                        'state' => 'ESTIMATE'
                    ]
                ]
            ],
            $tools->convert($data, "FIRAU")
        );
    }

    public function testInboundActualPilotageConfirmedParsing(): void
    {
        $json = '{
            "id": 456326,
            "vesselImo": 9387425,
            "vesselName": "Empire",
            "startTime": "2019-12-12T21:31:00Z",
            "endTime": "2019-12-12T22:34:00Z",
            "startCode": "FIRAE",
            "endCode": "FIRAU",
            "state": "ORDER"
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
                "imo"           => 9387425,
                "vessel_name"   => "Empire",
                "time_type"     => "Planned",
                "state"         => "Pilotage_Commenced",
                "time"          => "2019-12-12T21:31:00Z",
                "payload"       =>  [
                    "fromPoint"   => "FIRAE",
                    "toPoint"     => "FIRAU",
                    "direction"   => "inbound",
                    "original_message" => [
                        'id' => 456326,
                        'vesselImo' => '9387425',
                        'vesselName' => 'Empire',
                        'startTime' => '2019-12-12T21:31:00Z',
                        'endTime' => '2019-12-12T22:34:00Z',
                        'startCode' => 'FIRAE',
                        'endCode' => 'FIRAU',
                        'state' => 'ORDER'
                    ]
                ]
            ],
            $tools->convert($data, "FIRAU")
        );
    }

    public function testInboundActualPilotageCommencedParsing(): void
    {
        $json = '{
            "id": 456326,
            "vesselImo": 9387425,
            "vesselName": "Empire",
            "startTime": "2019-12-12T21:31:00Z",
            "endTime": "2019-12-12T22:34:00Z",
            "startCode": "FIRAE",
            "endCode": "FIRAU",
            "state": "ACTIVE"
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
                "imo"           => 9387425,
                "vessel_name"   => "Empire",
                "time_type"     => "Actual",
                "state"         => "Pilotage_Commenced",
                "time"          => "2019-12-12T21:31:00Z",
                "payload"       =>  [
                    "fromPoint"   => "FIRAE",
                    "toPoint"     => "FIRAU",
                    "direction"   => "inbound",
                    "original_message" => [
                        'id' => 456326,
                        'vesselImo' => '9387425',
                        'vesselName' => 'Empire',
                        'startTime' => '2019-12-12T21:31:00Z',
                        'endTime' => '2019-12-12T22:34:00Z',
                        'startCode' => 'FIRAE',
                        'endCode' => 'FIRAU',
                        'state' => 'ACTIVE'
                    ]
                ]
            ],
            $tools->convert($data, "FIRAU")
        );
    }

    public function testInboundActualPilotageCompletedParsing(): void
    {
        $json = '{
            "id": 456326,
            "vesselImo": 9387425,
            "vesselName": "Empire",
            "startTime": "2019-12-12T21:31:00Z",
            "endTime": "2019-12-12T22:34:00Z",
            "startCode": "FIRAE",
            "endCode": "FIRAU",
            "state": "FINISHED"
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
                "imo"           => 9387425,
                "vessel_name"   => "Empire",
                "time_type"     => "Actual",
                "state"         => "Pilotage_Completed",
                "time"          => "2019-12-12T22:34:00Z",
                "payload"       => [
                    "fromPoint"   => "FIRAE",
                    "toPoint"     => "FIRAU",
                    "direction"   => "inbound",
                    "original_message" => [
                        'id' => 456326,
                        'vesselImo' => '9387425',
                        'vesselName' => 'Empire',
                        'startTime' => '2019-12-12T21:31:00Z',
                        'endTime' => '2019-12-12T22:34:00Z',
                        'startCode' => 'FIRAE',
                        'endCode' => 'FIRAU',
                        'state' => 'FINISHED'
                    ]
                ]
            ],
            $tools->convert($data, "FIRAU")
        );
    }

    public function testOutboundActualPilotageOrderedParsing(): void
    {
        $json = '{
            "id": 456326,
            "vesselImo": 9387425,
            "vesselName": "Empire",
            "startTime": "2019-12-12T21:31:00Z",
            "endTime": "2019-12-12T22:34:00Z",
            "startCode": "FIRAU",
            "endCode": "FIRAE",
            "state": "ORDER"
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
                "imo"           => 9387425,
                "vessel_name"   => "Empire",
                "time_type"     => "Planned",
                "state"         => "Pilotage_Commenced",
                "time"          => "2019-12-12T21:31:00Z",
                "payload"       => [
                    "fromPoint"   => "FIRAU",
                    "toPoint"     => "FIRAE",
                    "direction"   => "outbound",
                    "original_message" => [
                        'id' => 456326,
                        'vesselImo' => '9387425',
                        'vesselName' => 'Empire',
                        'startTime' => '2019-12-12T21:31:00Z',
                        'endTime' => '2019-12-12T22:34:00Z',
                        'startCode' => 'FIRAU',
                        'endCode' => 'FIRAE',
                        'state' => 'ORDER'
                    ]

                ]
            ],
            $tools->convert($data, "SEGVX")
        );
    }
    public function testOutboundActualPilotageCommencedParsing(): void
    {
        $json = '{
            "id": 456326,
            "vesselImo": 9387425,
            "vesselName": "Empire",
            "startTime": "2019-12-12T21:31:00Z",
            "endTime": "2019-12-12T22:34:00Z",
            "startCode": "FIRAU",
            "endCode": "FIRAE",
            "state": "ACTIVE"
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
                "imo"           => 9387425,
                "vessel_name"   => "Empire",
                "time_type"     => "Actual",
                "state"         => "Pilotage_Commenced",
                "time"          => "2019-12-12T21:31:00Z",
                "payload"       => [
                    "fromPoint"   => "FIRAU",
                    "toPoint"     => "FIRAE",
                    "direction"   => "outbound",
                    "original_message" => [
                        'id' => 456326,
                        'vesselImo' => '9387425',
                        'vesselName' => 'Empire',
                        'startTime' => '2019-12-12T21:31:00Z',
                        'endTime' => '2019-12-12T22:34:00Z',
                        'startCode' => 'FIRAU',
                        'endCode' => 'FIRAE',
                        'state' => 'ACTIVE'
                    ]
                ]
            ],
            $tools->convert($data, "SEGVX")
        );
    }
    public function testOutboundActualPilotageCompletedParsing(): void
    {
        $json = '{
            "id": 456326,
            "vesselImo": 9387425,
            "vesselName": "Empire",
            "startTime": "2019-12-12T21:31:00Z",
            "endTime": "2019-12-12T22:34:00Z",
            "startCode": "FIRAU",
            "endCode": "FIRAE",
            "state": "FINISHED"
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
                "imo"           => 9387425,
                "vessel_name"   => "Empire",
                "time_type"     => "Actual",
                "state"         => "Pilotage_Completed",
                "time"          => "2019-12-12T22:34:00Z",
                "payload"       => [
                    "fromPoint"   => "FIRAU",
                    "toPoint"     => "FIRAE",
                    "direction"   => "outbound",
                    "original_message" => [
                        'id' => 456326,
                        'vesselImo' => '9387425',
                        'vesselName' => 'Empire',
                        'startTime' => '2019-12-12T21:31:00Z',
                        'endTime' => '2019-12-12T22:34:00Z',
                        'startCode' => 'FIRAU',
                        'endCode' => 'FIRAE',
                        'state' => 'FINISHED'
                    ]
                ]
            ],
            $tools->convert($data, "SEGVX")
        );
    }
    public function testEmptyImo()
    {
        $json = '{
            "id": 456326,
            "vesselImo": "",
            "vesselName": "Empire",
            "startTime": "2019-12-12T21:31:00Z",
            "endTime": "2019-12-12T22:34:00Z",
            "startCode": "FIRAU",
            "endCode": "FIRAE",
            "state": "FINISHED"
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
                "imo"           => 0,
                "vessel_name"   => "Empire",
                "time_type"     => "Actual",
                "state"         => "Pilotage_Completed",
                "time"          => "2019-12-12T22:34:00Z",
                "payload"       => [
                    "fromPoint"   => "FIRAU",
                    "toPoint"     => "FIRAE",
                    "direction"   => "outbound",
                    "original_message" => [
                        'id' => 456326,
                        'vesselImo' => '',
                        'vesselName' => 'Empire',
                        'startTime' => '2019-12-12T21:31:00Z',
                        'endTime' => '2019-12-12T22:34:00Z',
                        'startCode' => 'FIRAU',
                        'endCode' => 'FIRAE',
                        'state' => 'FINISHED'
                    ]
                ]
            ],
            $tools->convert($data, "SEGVX")
        );
    }
    public function testCancelledStatusIsNotRead()
    {
        $json = '{
            "id": 456326,
            "vesselImo": "9387425",
            "vesselName": "Empire",
            "startTime": "2019-12-12T21:31:00Z",
            "endTime": "2019-12-12T22:34:00Z",
            "startCode": "FIRAU",
            "endCode": "FIRAE",
            "state": "CANCELLED"
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
            ],
            $tools->convert($data, "SEGVX")
        );
    }
    public function testStartBerthIsReadAndSetToPayload()
    {
        $json = '{
            "id": 468246,
            "vesselImo": 9528495,
            "vesselName": "Nordic Erika",
            "startTime": "2020-06-11T21:17:00Z",
            "endTime": "2020-06-11T22:32:00Z",
            "startCode": "FIRAU",
            "endCode": "FIRAE",
            "state": "FINISHED",
            "startBerth": {
                "code": "P2",
                "name": "Petäjäs, Bulksatama"
            },
            "endBerth": null
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
                "imo"           => 9528495,
                "vessel_name"   => "Nordic Erika",
                "time_type"     => "Actual",
                "state"         => "Pilotage_Completed",
                "time"          => "2020-06-11T22:32:00Z",
                "payload"       => [
                    "fromPoint"   => "FIRAU",
                    "toPoint"     => "FIRAE",
                    "direction"   => "outbound",
                    "original_message" => [
                        'id' => 468246,
                        'vesselImo' => 9528495,
                        'vesselName' => 'Nordic Erika',
                        'startTime' => '2020-06-11T21:17:00Z',
                        'endTime' => '2020-06-11T22:32:00Z',
                        'startCode' => 'FIRAU',
                        'endCode' => 'FIRAE',
                        'state' => 'FINISHED',
                        "startBerth" => [
                            "code" => "P2",
                            "name" => "Petäjäs, Bulksatama"
                        ],
                        "endBerth" => null
                    ],
                    "berth_name" => "Petäjäs, Bulksatama"
                ]
            ],
            $tools->convert($data, "SEGVX")
        );
    }
    public function testEndBerthIsReadAndSetToPayload()
    {
        $json = '{
            "id": 468246,
            "vesselImo": 9528495,
            "vesselName": "Nordic Erika",
            "startTime": "2020-06-11T21:17:00Z",
            "endTime": "2020-06-11T22:32:00Z",
            "startCode": "FIRAE",
            "endCode": "FIRAU",
            "state": "FINISHED",
            "startBerth": null,
            "endBerth": {
              "code": "P2",
              "name": "Petäjäs, Bulksatama"
            }
        }';
        $data = json_decode($json, true);

        $tools = new PilotOrderTools();
        $this->assertEquals(
            [
                "imo"           => 9528495,
                "vessel_name"   => "Nordic Erika",
                "time_type"     => "Actual",
                "state"         => "Pilotage_Completed",
                "time"          => "2020-06-11T22:32:00Z",
                "payload"       => [
                    "fromPoint"   => "FIRAE",
                    "toPoint"     => "FIRAU",
                    "direction"   => "outbound",
                    "original_message" => [
                        'id' => 468246,
                        'vesselImo' => 9528495,
                        'vesselName' => 'Nordic Erika',
                        'startTime' => '2020-06-11T21:17:00Z',
                        'endTime' => '2020-06-11T22:32:00Z',
                        'startCode' => 'FIRAE',
                        'endCode' => 'FIRAU',
                        'state' => 'FINISHED',
                        "startBerth" => null,
                        "endBerth" => [
                          "code" => "P2",
                          "name" => "Petäjäs, Bulksatama"
                        ]
                    ],
                    "berth_name" => "Petäjäs, Bulksatama"
                ]
            ],
            $tools->convert($data, "SEGVX")
        );
    }
}

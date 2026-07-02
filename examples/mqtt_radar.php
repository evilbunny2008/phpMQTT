#!/usr/bin/php
<?php

	require_once("common.php");

	$lights_on = 0;
	$radar_01 = 0;
	$radar_02 = 0;
	$radar_03 = 0;

	$base_topic = "zigbee2mqtt";
	$lights = array("Light_01" => "state_l2", "Light_02" => "state", "Light_03" => "state");

	subscribeAndWait(array("#"));

	function procMsg($topic, $msg)
	{
		global $lights, $lights_on, $radar_01, $radar_02, $radar_03;

		if(!str_contains($topic, "/Radar_") || $msg === null || trim($msg) == "")
			return;

		$id = substr($topic, -3);

		$obj = json_decode($msg, true);

		if($id != "_01" && $id != "_02")
			return;

		if($obj === null || !is_array($obj) || count($obj) <= 0)
			return;

		if(!isset($obj["occupancy"]))
			return;

		$occupancy = intval($obj["occupancy"]);

		if($id == "_01" && $occupancy == 1)
		{
			// TODO check lux before turning the lights on during the day
			echo "turn lights on\n";
			foreach($lights as $light => $state)
			{
				$topic = $base_topic . "/" . $light . "/set";
				$msg = array();
				$msg[$state] = "ON";

				$payload = json_encode($msg);

				echo "Sending $payload to $topic\n";
				mqttpublish($topic, $payload);
			}

			$lights_on = 1;
			$radar_01 = 1;
			return;
		}

		else if($id == "_02" && $occupancy == 1)
		{
			$radar_02 = 1;
			return;
		}

		else if($id == "_02" && $occupancy == 0)
		{
			$radar_02 = 0;
			return;
		}

		else if($id == "_01" && $occupancy == 0 && $radar_02 == 0 && $lights_on == 1)
		{
			foreach($lights as $light => $state)
			{
				$topic = $base_topic . "/" . $light . "/set";
				$msg = array();
				$msg[$state] = "OFF";

				$payload = json_encode($msg);
				mqttpublish($topic, $payload);
			}

			$lights_on = 0;
			$radar_01 = 0;
			return;
		}
	}

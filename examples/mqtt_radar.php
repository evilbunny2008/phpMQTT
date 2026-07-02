#!/usr/bin/php
<?php
	require_once("/usr/src/phpMQTT/examples/credentials.php");
	require_once("/usr/src/phpMQTT/examples/MQTThelper.php");

	subscribeAndWait(uniqid(), $subs, true);

	function procMsg_Cluster1($topic, $message)
	{
		global $cluster1_base_topic, $light_cluster1, $light_cluster1_check, $radar_01, $radar_02;

		echo "procMsg_Cluster1() \$topic == $topic\n";

		if($message == null)
		{
			echo "\$message == null\n";
			return;
		}

		if(trim($message) == "")
		{
			echo "trim(\$message) == ''\n";
			return;
		}

		$id = substr($topic, -3);

//		echo "\$id == ";
//		var_dump($id);

		$obj = json_decode($message, true);

		if($obj == null)
		{
			echo "\$obj is null\n";
			return;
		}

		if(!is_array($obj))
		{
			echo "\$obj is not an array\n";
			return;
		}

		if(count($obj) == 0)
		{
			echo "count(\$obj) is zero\n";
			return;
		}

		if(!isset($obj["occupancy"]))
		{
			echo "\$obj['occupancy'] is not set\n";
			return;
		}

		$illuminance = intval($obj["illuminance"]);

		$illuminance_raw = intval($obj["illuminance_raw"]);

		$occupancy = intval($obj["occupancy"]);

		if(false)
		{
			echo "\$topic = ";
			var_dump($topic);

			echo "\$illuminance = ";
			var_dump($illuminance);

			echo "\$illuminance_raw = ";
			var_dump($illuminance_raw);

			echo "\$occupancy = ";
			var_dump($occupancy);
		}

		if($id == "_01" && $occupancy == 1 && $illuminance_raw < 2000)
		{
			echo "turn cluster1 lights on\n";

			if(isLight("mqtt_radar_cluster1_lights_on", $light_cluster1_check))
			{
				echo "Cluster1 light already on, skipping...\n";
				return;
			}

			foreach($light_cluster1 as $light => $state)
			{
				$topic = $light . "/set";
				$msg = array();
				$msg[$state] = "ON";

				$payload = json_encode($msg);

				echo "Sending $payload to $topic\n";
				mqttpublish(uniqid(), $topic, $payload, true, true);
			}

			$radar_01 = 1;
			return;
		}

		else if($id == "_02" && $occupancy == 1 && $radar_01 == 1)
		{
			//echo "Radar 2 detected presence, resetting light trigger...\n";
			$radar_02 = 1;
			return;
		}

		else if($id == "_02" && $occupancy == 0 && $radar_02 == 1 && $radar_01 == 1)
		{
			//echo "Radar 2 lost presence, arm laundry lights out trigger...\n";
			$radar_02 = 0;
			return;
		}

		else if($id == "_01" && $occupancy == 0 && $radar_02 == 0 && $radar_01 == 1)
		{
			if(isLight("mqtt_radar_cluster1_lights_on", $light_cluster1_check, "state", "OFF"))
			{
				echo "Lights already off, skipping...\n";
				return;
			}

			echo "Turn cluster1 lights off\n";
			foreach($light_cluster1 as $light => $state)
			{
				$topic = $light . "/set";
				$msg = array();
				$msg[$state] = "OFF";

				$payload = json_encode($msg);

				echo "Sending $payload to $topic\n";
				mqttpublish(uniqid(), $topic, $payload, true, true);
			}

			$radar_01 = 0;
			return;
		}
	}

	function procMsg_Cluster2($topic, $message)
	{
		global $light_cluster2, $radar_03;

		echo "procMsg_Cluster2() \$topic == $topic\n";

		if($message == null)
		{
			echo "\$message == null\n";
			return;
		}

		if(trim($message) == "")
		{
			echo "trim(\$message) == ''\n";
			return;
		}

		$id = substr($topic, -3);

//		echo "\$id == ";
//		var_dump($id);

		$obj = json_decode($message, true);

		if($obj == null)
		{
			echo "\$obj is null\n";
			return;
		}

		if(!is_array($obj))
		{
			echo "\$obj is not an array\n";
			return;
		}

		if(count($obj) == 0)
		{
			echo "count(\$obj) is zero\n";
			return;
		}

		if(!isset($obj["occupancy"]))
		{
			echo "\$obj['occupancy'] is not set\n";
			return;
		}

		$illuminance = intval($obj["illuminance"]);

		$illuminance_raw = intval($obj["illuminance_raw"]);

		$occupancy = intval($obj["occupancy"]);

		if(false)
		{
			echo "\$topic = ";
			var_dump($topic);

			echo "\$illuminance = ";
			var_dump($illuminance);

			echo "\$illuminance_raw = ";
			var_dump($illuminance_raw);

			echo "\$occupancy = ";
			var_dump($occupancy);
		}

		if($id == "_03" && $occupancy == 1 && $illuminance_raw < 2000)
		{
			echo "turn cluster2 lights on\n";

			if(isLight("mqtt_radar_cluster2_lights_on", $light_cluster2_check))
			{
				echo "Cluster2 lights already on, skipping...\n";
				return;
			}

			foreach($light_cluster2 as $light => $state)
			{
				$topic = $light . "/set";
				$msg = array();
				$msg[$state] = "ON";

				$payload = json_encode($msg);

				echo "Sending $payload to $topic\n";
				mqttpublish(uniqid(), $topic, $payload, true, true);
			}

			$radar_03 = 1;
			return;
		}
		else if($id == "_03" && $occupancy == 0 && $radar_03 == 1)
		{
			if(isLight("mqtt_radar_cluster2_lights_on", $light_cluster2_check, "state", "OFF"))
			{
				echo "Cluster2 lights already off, skipping...\n";
				return;
			}

			echo "Turn the hallway lights off\n";
			foreach($light_cluster2 as $light => $state)
			{
				$topic = $light . "/set";
				$msg = array();
				$msg[$state] = "OFF";

				$payload = json_encode($msg);

				echo "Sending $payload to $topic\n";
				mqttpublish(uniqid(), $topic, $payload, true, true);
			}

			$radar_03 = 0;
			return;
		}
	}

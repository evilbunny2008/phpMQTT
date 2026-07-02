<?php
	require_once("/usr/src/phpMQTT/phpMQTT.php");

	function mqttconnect($client_id, $debug=false)
	{
		global $hostname, $port, $ssl_cert, $username, $password;

		if(false)
		{
			echo "\$hostname == $hostname\n";
			echo "\$port == $port\n";
			echo "\$username == $username\n";
			echo "\$password == $password\n";
			echo "\$ssl_cert == $ssl_cert\n";
			exit;
		}

		if(isset($ssl_cert))
			$mqtt = new Bluerhinos\phpMQTT($hostname, $port, $client_id, $ssl_cert);
		else
			$mqtt = new Bluerhinos\phpMQTT($hostname, $port, $client_id);

		$mqtt->debug = $debug;

		if($mqtt->connect(false, null, $username, $password))
			return $mqtt;

		echo "Time out!\n";
		return false;

	}

	function mqttpublish($client_id, $topic, $payload, $retain=true, $debug=false)
	{
		$mqtt = mqttconnect($client_id, $debug);

		if($mqtt === false)
			return false;

		if(is_array($payload))
			$payload = json_encode($payload);

		//echo "Topic: $topic\nPayload: $payload\n";

		$mqtt->publish($topic, $payload, 0, $retain);
		$mqtt->close();
	}

	function subscribeAndWait($client_id, $subs, $debug=false)
	{
		$mqtt = mqttconnect($client_id, $debug);

		if(!$mqtt)
			return false;

		$sub_topics = array();
		foreach($subs as $sub)
		{
			foreach($sub["topics"] as $topic)
			{
				if($debug)
					echo "Subscribing to $topic and binding to ${sub['function_name']}\n";

				$sub_topics[$topic] = array("qos" => 1, "function" => $sub["function_name"]);
			}
		}

		$mqtt->subscribe($sub_topics, 0);

		while($mqtt->proc()) {}

		$mqtt->close();
	}

	function mqttget($client_id, $topic, $debug=false)
	{
//echo "mqttget(): \$client_id == $client_id\n\n";

		$mqtt = mqttconnect($client_id, $debug);

		if(!$mqtt)
			return false;

		$msg = null;

		if($debug)
			echo "Connected to mqtt server...\n";

		$topics = array();
		$topics[$topic] = array("qos" => 1, "function" => "__direct_return_message__");

		if($debug)
			echo "Reading $topic\n\n";

		$msg = $mqtt->subscribe($topics, 1);

		$msg = null;
		$now = time();

		do
		{
			$msg = $mqtt->proc();

//			echo "\n\nvar_dump(\$msg) == ";
//			var_dump($msg);
//			echo "\n\n";

			$msg = trim($msg);

			if(strlen($msg) >= 5)
				break;
		} while($msg == true && time() - $now <= 1);

		$mqtt->close();

		if(strlen($msg) >= 5)
			return $msg;

		return null;
	}

	function isLight($client_id, $topic, $which="state", $state="ON", $debug=false)
	{
		$ret = mqttget($client_id, $topic, $debug);

//echo "\$ret == $ret\n\n";

		if($ret == null || trim($ret) == "")
			return false;

		$obj = json_decode($ret, true);
		if($obj == null || !is_array($obj) || count($obj) == 0 || $obj[$which] != $state)
			return false;

		return true;
	}

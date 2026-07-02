<?php
/*
	$hostname = "mqtt.example.com";
	$port = 1883;
	$username = "username";
	$password = "password";

	$light_cluster1 = array("Light_01" => "state_l2", "Light_02" => "state", "Light_03" => "state");
	$light_cluster2 = array("Socket_04" => "state", "Socket_05" => "state");

	$light_cluster1_check = "z2m2/Light_03";
	$light_cluster2_check = "z2m2/Socket_04";

	$subs = array();
	$subs[] = array("topics" => array("zigbee2mqtt/Radar_01", "zigbee2mqtt/Radar_02"), "function_name" => "procMsg_Cluster1");
	$subs[] = array("topics" => array("zigbee2mqtt/Radar_03"), "function_name" => "procMsg_Cluster2");

	$radar_01 = 0;
	$radar_02 = 0;
	$radar_03 = 0;

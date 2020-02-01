<?php

//1. load XML file info here!
$xml = simplexml_load_file('config/connections.xml');

//2. Open N clients terminals with running script 
foreach($xml->clients->client as $client_info){
	$terminal_command = "osascript -e 'tell application \"Terminal\" to do script \"cd ~/Documents/CMPSC_271/replicated-blockchain/ && php client.php " . $client_info->port . " " . $client_info->name . " " . $client_info->ip . " " . $client_info->index . " " . "\" ' ";

	$run = system($terminal_command, $val);
	sleep(5);
}
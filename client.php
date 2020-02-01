<?php

include "helper.php";

echo PHP_EOL . 
	'*****************************************************' . PHP_EOL
 	. 'Client Name: ' . $argv[2] . PHP_EOL . 
 	'*****************************************************' . PHP_EOL . PHP_EOL;


$local_client_name = $argv[2]; 
$local_ip = $argv[3]; 
$local_port = $argv[1];
$local_2d_index = $argv[4];


$clients_list = helper::list_clients();
$connections = array();

$local_time = 0;
//client's 2D table~
$table = array(
		//A, B, C
	array(0, 0, 0), //A 
	array(0, 0, 0), //B
	array(0, 0, 0), //C
);

//$2D Table order: 
/*
__|_A_|_B_|_C_
A |   |   |   
--+---+---+---
B |   |   |   
--+---+---+---
C |___|___|___
*/

//initialize local blockchain log.
$local_chain = array(
	//array(
	//	"from" => "__ini__",
	//	"to" => $local_client_name, 
	//	"amount" => 10, 
	//  "time" => 0,
	//)
);

foreach($clients_list as $c_info){
	if($c_info['name'] == $local_client_name){
		continue;
	}

	//try to actively connect!
	sleep(3);

	echo "[Active Socket]Connecting to the client {$c_info['name']} > {$c_info['ip']}:{$c_info['port']}" . PHP_EOL;

	$sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
	$result = @socket_connect($sock, $c_info['ip'], $c_info['port']);
	
	//if connection TRUE, put it back in array!
	if($result){
		echo "[Active Socket]Connected to the client {$c_info['name']}" . PHP_EOL;
		@socket_write($sock, $local_client_name, strlen($local_client_name));
		socket_set_nonblock($sock);
		$connections[$c_info['name']] = $sock;
	} else {
		echo "[Active Socket]Cannot reach the client {$c_info['name']}" . PHP_EOL;
		#echo PHP_EOL . 'Error_code: ' . socket_last_error() . PHP_EOL;
	}
}

echo PHP_EOL . 'Client enters [Passive] connecting mode......' . PHP_EOL;
$passive_sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
socket_bind($passive_sock, $local_ip, $local_port);
socket_listen($passive_sock, 24);
socket_set_nonblock($passive_sock);
while (1) {
	$spawn = socket_accept($passive_sock);
	if($spawn){
		$input = @socket_read($spawn, 1024);
		$input = trim($input);

		echo PHP_EOL . "[Passive Socket]: received a socket connection from the client {$input}.". PHP_EOL; 
		$connections[$input] = $spawn;
	} else {
		//echo '. ';
		sleep(1);
	}

	if(count($connections) == count($clients_list) - 1){
		echo PHP_EOL . "All connections have been established!" . PHP_EOL;
		break;
	}
}

echo "Clients Connections: ";
print_r($connections);

echo "Please type in command to perform Blockchian Transaction and Message Sending. " . PHP_EOL;
echo "Ex. A 10 --> send $10 to the client A" . PHP_EOL;
echo "Ex. A -m I Just Sent You Money --> send the message 'I Just Send You Money' to the client A" . PHP_EOL . PHP_EOL;


echo "Current Balance: " . helper::read_balance($local_chain, $local_client_name) . PHP_EOL . PHP_EOL;

$stdin = fopen('php://stdin', 'r');
$result = stream_set_blocking($stdin, false);

//non-blocking reading user's console input from here~
while(1) {
    $x = "";
	$x = trim((string)fgets($stdin), "\n");
    if(strlen($x) > 0) {
    	//Type in format: Client balance. 
		//Ex. B 10 ==> it means that give $10 from this client to B
		$x = explode(' ', $x);
    	//TODO: perform validation here!
    	$send_to = $x[0];

    	if($send_to == $local_client_name || $connections[$send_to] === NULL){
    		echo "Invalid Client! " . PHP_EOL;
    		continue;
    	}

    	if($x[1] == "-m"){
    		//this is a message!
    		unset($x[0]);
    		unset($x[1]); 
    		$msg = implode(" ", $x);

    		//????? TODO: DO I NEED TO INCREASE local time???
    		//$local_time ++; 
    		$table[$local_2d_index][$local_2d_index] = $local_time;
    		//send msg with 2D table info!

			$partial_log = helper::obtain_partial_blockchain($table, $local_2d_index, $clients_list[$send_to]["2d_index"], $local_chain);

    		$send_body = array(
    			'time_table' => $table, 
    			'msg' => $msg,
    			'from' => $local_client_name,
    			'original_client_2d_index' => $local_2d_index, 
    			'partial_block_chain' => $partial_log, 
    		);

    		$json_string = json_encode($send_body);
    		socket_write($connections[$send_to], $json_string, strlen($json_string));

    	} else {	
    		$amount = $x[1];

    		$local_time ++; 
    		$table[$local_2d_index][$local_2d_index] = $local_time;
    		//1. check locak amount!
			$local_chain[] = array(
				"from" => $local_client_name,
				"to" => $send_to,
				"amount" => intval($amount), 
				//"original_client" => $local_client_name, 
				"original_client_2d_index" => $local_2d_index, 
				"original_time" => $local_time,
			);

			echo "Local Time Table: " . PHP_EOL; 
			helper::pretty_print_table($table);
			echo PHP_EOL;
			//print_r($local_chain);
    	}
    } else {
        //loop through each socket and check if there is any message from others!
    	foreach($connections as $c_name => $sock){
    		#echo "Before Socket Read ---  ";
    		$input = socket_read($sock, 1024);
    		$input = trim($input, "\n");
    		if($input){
    			//process info here.
    			$body = json_decode($input, true);

    			//1. update local time if necessary.

    			//2. display msg info
    			$msg = $body["msg"];

    			echo "Client $c_name sent me a message: " . $msg . PHP_EOL;

    			//3. process time table. 
    			$received_time_table = $body["time_table"];

    			//4. insert partial blockchian.
    			if($body['partial_block_chain']){
    				foreach ($body['partial_block_chain'] as $log) {
    					$local_chain[] = $log;
    				}
    			}

    			helper::replicate_time_table($received_time_table, $body['original_client_2d_index'], $local_2d_index, $table);

    			print_r($local_chain);
    			//helper::pretty_print_table($table);
	    	}
    	}
    }

}
















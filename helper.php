<?php

class helper {
	
	public static function list_clients(){
		$xml = simplexml_load_file(__DIR__.'/config/connections.xml');
		$clients_info = array();

		foreach($xml->clients->client as $client_info){
			$clients_info[(string)$client_info->name] = array(
				'name' => (string)$client_info->name,
				'ip' => (string)$client_info->ip, 
				'port' => (int)$client_info->port, 
				'2d_index' => (int)$client_info->index
			);
		} 

		return $clients_info;
	}

	public static function read_balance($local_chain, $client_name, $starting_balance = 10){
		if($client_name){
   			$balance = 0;
   			//client_name
   			$calc = array_reduce($local_chain, function($balance, $trx) use ($client_name){
   				if($trx['from'] == $client_name){
   					$balance -= $trx['amount'];
   				} elseif($trx['to'] == $client_name) {
					$balance += $trx['amount'];
   				}
   				return $balance;
   			});
	   		return $calc + $starting_balance;
   		}
	}

	//this function will figure out what should be sent to the to_2d_index client.
	public static function obtain_partial_blockchain($table, $local_2d_index, $to_2d_index, $blockchain){
		$local_row = $table[$local_2d_index];
		$to_row = $table[$to_2d_index];

		$filter = array();
		foreach($to_row as $index => $time){
			if($index == $to_2d_index){
				//ignore the receiver's record.
				continue;
			}

			if($local_row[$index] > $time){
				$filter[$index] = array(
					"max" => $local_row[$index], 
					"min" => $time
				);
			}
		}

		//print_r($filter);

		//then search block chain based on the calculated filter~
		$ret = array();

		foreach ($blockchain as $log) {
			if(@$filter[$log["original_client_2d_index"]] !== NULL){
				//yeah, this client's record actually exist in blockchain.
				if($log["original_time"] > $filter[$log["original_client_2d_index"]]["min"] && $log["original_time"] <= $filter[$log["original_client_2d_index"]]["max"]){
					$ret[] = $log;
				}
			}
		}

		return $ret;
	}

	public static function replicate_time_table($received_table, $from_client_index, $local_clinet_index, &$local_table, $print = true){
		//foreach($received_table[$from_client_index] as $idx => $time){
		//	if($local_table[$from_client_index][$idx] < $time){
		//		$local_table[$from_client_index][$idx] = $time;
		//	}
		//}

		$clone = $local_table;

		foreach($received_table as $row_num => $row){
			foreach($row as $col_num => $col){
				if($col > $local_table[$row_num][$col_num]){
					$local_table[$row_num][$col_num] = $col;
				}
			}
		}

		foreach($received_table[$from_client_index] as $idx => $time){
			if($local_table[$local_clinet_index][$idx] < $time){
				$local_table[$local_clinet_index][$idx] = $time;
			}
		}

		if($print){
			echo PHP_EOL; 
			echo "__|_A_|_B_|_C_        __|_A_|_B_|_C_ " . PHP_EOL; 
			echo "A | {$clone[0][0]} | {$clone[0][1]} | {$clone[0][2]}         A | {$local_table[0][0]} | {$local_table[0][1]} | {$local_table[0][2]}" . PHP_EOL;
			echo "--+---+---+---        --+---+---+---" . PHP_EOL;
			echo "B | {$clone[1][0]} | {$clone[1][1]} | {$clone[1][2]}   ==>   B | {$local_table[1][0]} | {$local_table[1][1]} | {$local_table[1][2]}" . PHP_EOL;
			echo "--+---+---+---        --+---+---+---" . PHP_EOL;
			echo "C | {$clone[2][0]} | {$clone[2][1]} | {$clone[2][2]}         C | {$local_table[2][0]} | {$local_table[2][1]} | {$local_table[2][2]}" . PHP_EOL;
			echo "--------------        --------------" . PHP_EOL;
		}	
	}

	public static function pretty_print_table($table){
		echo PHP_EOL . "__|_A_|_B_|_C_" . PHP_EOL; 
		echo "A | {$table[0][0]} | {$table[0][1]} | {$table[0][2]} " . PHP_EOL;
		echo "--+---+---+---" . PHP_EOL;
		echo "B | {$table[1][0]} | {$table[1][1]} | {$table[1][2]} " . PHP_EOL;
		echo "--+---+---+---" . PHP_EOL;
		echo "C | {$table[2][0]} | {$table[2][1]} | {$table[2][2]} " . PHP_EOL;
		echo "--------------" . PHP_EOL;
	}

}









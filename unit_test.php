<?php
include "helper.php";

$local_chain = array(
	array(
		"from" => "A",
		"to" => "B",
		"amount" => 1, 
		"original_client" => "A", 
		"original_client_2d_index" => 0, 
		"original_time" => 0,
	), 

	array(
		"from" => "A",
		"to" => "B",
		"amount" => 2, 
		"original_client" => "A", 
		"original_client_2d_index" => 0, 
		"original_time" => 1,
	), 

	array(
		"from" => "A",
		"to" => "B",
		"amount" => 3, 
		"original_client" => "A", 
		"original_client_2d_index" => 0, 
		"original_time" => 2,
	), 

	array(
		"from" => "A",
		"to" => "B",
		"amount" => 4, 
		"original_client" => "A", 
		"original_client_2d_index" => 0, 
		"original_time" => 3,
	), 

	array(
		"from" => "A",
		"to" => "B",
		"amount" => 5, 
		"original_client" => "A", 
		"original_client_2d_index" => 0, 
		"original_time" => 4,
	), 

	array(
		"from" => "C",
		"to" => "B",
		"amount" => 2, 
		"original_client" => "C", 
		"original_client_2d_index" => 2, 
		"original_time" => 1,
	), 

	array(
		"from" => "C",
		"to" => "B",
		"amount" => 3, 
		"original_client" => "C", 
		"original_client_2d_index" => 2, 
		"original_time" => 2,
	), 

);

$table = array(
		//A, B, C
	array(3, 0, 1), //A 
	array(3, 1, 0), //B
	array(0, 0, 0), //C
);

$table2 = array(
		//A, B, C
	array(3, 6, 1), //A 
	array(3, 1, 0), //B
	array(0, 4, 0), //C
);


print_r(helper::obtain_partial_blockchain($table, 0, 1, $local_chain));


$table = array(
		//A, B, C
	array(3, 0, 0), //A 
	array(0, 0, 0), //B
	array(0, 0, 0), //C
);

$table2 = array(
		//A, B, C
	array(0, 0, 0), //A 
	array(0, 1, 0), //B
	array(0, 0, 0), //C
);

$table3 = array(
		//A, B, C
	array(0, 0, 0), //A 
	array(0, 0, 0), //B
	array(0, 0, 0), //C
);


//helper::pretty_print_table($table);

helper::replicate_time_table($table, 0, 1, $table2);

//helper::pretty_print_table($table2);

helper::replicate_time_table($table2, 1, 2,$table3);

//helper::pretty_print_table($table3);

$table3[2][2] = 1;

//helper::pretty_print_table($table3);

helper::replicate_time_table($table3, 2, 0, $table);

//helper::pretty_print_table($table);







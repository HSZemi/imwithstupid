<?php

/*
 * The first two methods defined in this file are required to manage a connection
 * with the database. The others return useful information about the gamestate or
 * change the gamestate, for example by modifying players or rounds.
 */
 
 define("USER", "iws");
 define("PASS", "iws");


function db_connect(){
	// establish connection with the database
	$link = mysql_connect("localhost", USER, PASS)
	or die("Keine Verbindung möglich: " . mysql_error());
// 	echo "Verbindung zum Datenbankserver erfolgreich<br/>";

	mysql_select_db("iwstest") or die("Auswahl der Datenbank fehlgeschlagen</br>");
	return $link;
}

function db_close($link){
	mysql_close($link);
}


function create_player($name){
	$query = "INSERT INTO iwsPlayers(name) VALUES ('" . mysql_real_escape_string($name) . "');";
	$result = mysql_query($query);
	if(!$result){
		echo "create_player: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}

function remove_player($name){
	$query = "DELETE FROM iwsPlayers WHERE name LIKE '" . mysql_real_escape_string($name) . "';";
	$result = mysql_query($query);
	if(!$result){
		echo "remove_player: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}

function create_round($number){
	$query = "INSERT INTO iwsRound(number) VALUES (" . mysql_real_escape_string($number) . ");";
	$result = mysql_query($query);
	if(!$result){
		echo "create_round: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}

function create_question($round, $number, $value){
	$query = "INSERT INTO iwsQuestion(round, number, value) VALUES (" 
		. mysql_real_escape_string($round) . ", " 
		. mysql_real_escape_string($number) . ", '" 
		. mysql_real_escape_string($value) . "');";
	
	$result = mysql_query($query);
	if(!$result){
		echo "create_question: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}

function create_answer($question_string, $value){
	// find question id
	$query = "SELECT id FROM iwsQuestion WHERE value LIKE '" .  mysql_real_escape_string($question_string) . "';";
	$result = mysql_query($query);
	if(!$result){
		echo "create_answer: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$question_id = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	create_answer_number($question_id["id"], $value);
}

function create_answer_number($question_id, $value){
	// find question id
	
	$query = "INSERT INTO iwsAnswer(question, value) VALUES ('" 
		. mysql_real_escape_string($question_id) . "', '"
		. mysql_real_escape_string($value) . "');";
	
	$result = mysql_query($query);
	if(!$result){
		echo "create_answer_number: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}

function add_answer_string($player, $question, $answer){

	if($answer == "") return;
	
	// find player id
	$query = "SELECT id FROM iwsPlayers WHERE name LIKE '" .  mysql_real_escape_string($player) . "';";
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_string: Anfrage 1 fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$player_id = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	// find question id
	$query = "SELECT id FROM iwsQuestion WHERE value LIKE '" .  mysql_real_escape_string($question) . "';";
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_string: Anfrage 2 fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$question_id = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	
	// find answer id
	$query = "SELECT id FROM iwsAnswer WHERE question = " . mysql_real_escape_string($question_id["id"]) . " AND value LIKE '" .  mysql_real_escape_string($answer) . "';";
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_string: Anfrage 3 fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer_id = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	

	// insert the player's answer
	$query = "REPLACE INTO iwsAnswers(player, answer) VALUES (" 
		. mysql_real_escape_string($player_id["id"]) . ", "
		. mysql_real_escape_string($answer_id["id"]) . ");";
	
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_string: Anfrage 4 fehlgeschlagen: " . mysql_error() . "<br/>";
	}	
}

function add_answer_number($player, $round, $question, $answer){

	if($answer == "") return;
	
	// find player id
	$query = "SELECT id FROM iwsPlayers WHERE name LIKE '" .  mysql_real_escape_string($player) . "';";
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_number: Anfrage 1 fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$player_id = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	// find question id
	$query = "SELECT id FROM iwsQuestion WHERE number = " .  mysql_real_escape_string($question) . " AND round = " .  mysql_real_escape_string($round) . ";";
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_number: Anfrage 2 fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$question_id = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	$counter = 0;
	do{
		// find answer id
		$query = "SELECT id FROM iwsAnswer WHERE question = " . mysql_real_escape_string($question_id["id"]) . " AND value LIKE '" .  mysql_real_escape_string($answer) . "';";
		$result = mysql_query($query);
		if(!$result){
			echo "add_answer_number: Anfrage 3 fehlgeschlagen: " . mysql_error() . "<br/>";
		}
		
		$answer_id = mysql_fetch_array($result, MYSQL_ASSOC);
		mysql_free_result($result);
		
		if($answer_id["id"] == ""){
			create_answer_number($question_id["id"], $answer);
		}
		
		$counter++;
	} while($answer_id["id"] == "" && $counter < 3);
	
	if($counter > 2){
		echo "infiniloop!";
	}
	
	// remove previous answer(s) of that round
	$query = "DELETE FROM iwsAnswers
		WHERE player IN (SELECT id FROM iwsPlayers WHERE name LIKE '"  .  mysql_real_escape_string($player) .  "')
		AND answer IN (SELECT iwsAnswer.id FROM iwsAnswer JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id WHERE iwsQuestion.round = " .  mysql_real_escape_string($round) . " AND iwsQuestion.number = " .  mysql_real_escape_string($question) . ");";
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_number: Anfrage 4 fehlgeschlagen: " . mysql_error() . "<br/>";
	}	

	// insert the player's answer
	$query = "REPLACE INTO iwsAnswers(player, answer) VALUES (" 
		. mysql_real_escape_string($player_id["id"]) . ", "
		. mysql_real_escape_string($answer_id["id"]) . ");";
	
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_number: Anfrage 5 ". mysql_real_escape_string($answer_id["id"]) ."fehlgeschlagen: " . mysql_error() . "<br/>";
	}	
}

function get_max_question_of_round($nr){
	$query = "SELECT MAX(number) AS max FROM iwsQuestion WHERE round = " . mysql_real_escape_string($nr) . ";";
	$result = mysql_query($query);
	if(!$result){
		echo "get_max_question_of_round: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	return $answer["max"];
}

function get_max_round(){
	$query = "SELECT MAX(number) AS max FROM iwsRound;";
	$result = mysql_query($query);
	if(!$result){
		echo "get_max_round: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	return $answer["max"];
}

function get_first_player(){
	$query = "SELECT name FROM iwsPlayers WHERE id IN (SELECT MIN(id) FROM iwsPlayers);";
	$result = mysql_query($query);
	if(!$result){
		echo "get_first_player: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	return $answer["name"];
}

?>
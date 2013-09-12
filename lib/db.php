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

function validate_string_for_mysql_html($string){
	return mysql_real_escape_string(htmlspecialchars($string, ENT_QUOTES | ENT_HTML401));
}


function create_player($name, $game){
	$query = "INSERT INTO iwsPlayers(name, game) VALUES ('" . validate_string_for_mysql_html($name) . "', ".intval($game).");";
	$result = mysql_query($query);
	if(!$result){
		echo "create_player: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}

function remove_player($name, $game){
	$query = "DELETE FROM iwsPlayers WHERE name LIKE '" . validate_string_for_mysql_html($name) . "' AND game = ".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "remove_player: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}


function create_question($round, $value, $game){
	$query = "INSERT INTO iwsQuestion(round, value, game) VALUES (" 
		. mysql_real_escape_string($round) . ", '" 
		. validate_string_for_mysql_html($value) . "', "
		. intval($game).");";
	
	$result = mysql_query($query);
	if(!$result){
		echo "create_question: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}

function delete_question($id){
	$query = "DELETE FROM iwsQuestion WHERE id=".intval($id);
	$result = mysql_query($query);
	if(!$result){
		echo "delete_question: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}

/*function create_answer_string($question_string, $value, $game){
	// find question id
	$query = "SELECT id FROM iwsQuestion WHERE value LIKE '" .  validate_string_for_mysql_html($question_string) . "' AND game=".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "create_answer: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$question_id = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	create_answer($question_id["id"], $value, $game);
}*/

function create_answer($question_id, $value, $game){
	// find question id
	
	$query = "INSERT INTO iwsAnswer(question, value, game) VALUES ('" 
		. intval($question_id) . "', '"
		. validate_string_for_mysql_html($value) . "', "
		. intval($game).");";
	
	$result = mysql_query($query);
	if(!$result){
		echo "create_answer_number: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}


function delete_answer($id){
	$query = "DELETE FROM iwsAnswer WHERE id=".intval($id);
	$result = mysql_query($query);
	if(!$result){
		echo "delete_answer: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}

function get_player_id($player_name, $game){
	$query = "SELECT id FROM iwsPlayers WHERE name LIKE '" .  validate_string_for_mysql_html($player_name) . "' AND game=".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "get_player_id: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
		return -1;
	} else {
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$player_id = $row['id'];
		mysql_free_result($result);
		return $player_id;
	}
}

/*function add_answer_string($player, $question, $answer, $game){

	if($answer == "") return;
	
	// find player id
	$query = "SELECT id FROM iwsPlayers WHERE name LIKE '" .  validate_string_for_mysql_html($player) . "' AND game=".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_string: Anfrage 1 fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$player_id = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	// find question id
	$query = "SELECT id FROM iwsQuestion WHERE value LIKE '" .  validate_string_for_mysql_html($question) . "' AND game=".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_string: Anfrage 2 fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$question_id = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	
	// find answer id
	$query = "SELECT id FROM iwsAnswer WHERE question = " . mysql_real_escape_string($question_id["id"]) . " AND value LIKE '" .  validate_string_for_mysql_html($answer) . "' AND game=".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_string: Anfrage 3 fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer_id = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	

	// insert the player's answer
	$query = "REPLACE INTO iwsAnswers(player, answer, game) VALUES (" 
		. mysql_real_escape_string($player_id["id"]) . ", "
		. mysql_real_escape_string($answer_id["id"]) . ", "
		. intval($game).");";
	
	$result = mysql_query($query);
	if(!$result){
		echo "add_answer_string: Anfrage 4 fehlgeschlagen: " . mysql_error() . "<br/>";
	}	
}*/

function get_answer_id($question_id, $answer_string, $game){
	$query = "SELECT id FROM iwsAnswer WHERE question = " . intval($question_id) . " AND value LIKE '" . validate_string_for_mysql_html($answer_string) . "' AND game=".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "get_answer_id: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
		return -1;
	} else {
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		mysql_free_result($result);
		
		if(isset($row['id'])){
			return $row['id'];
		} else {
			return -1;
		}
	}
}

function remove_answers_for_question($player_id, $question_id, $game){
	$query = "DELETE FROM iwsAnswers WHERE player=".intval($player_id)." AND answer IN (SELECT id FROM iwsAnswer WHERE question=".intval($question_id)." AND game=".intval($game).");";
	$result = mysql_query($query);
	if(!$result){
		echo "remove_answers_for_question: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
	}	
}

function insert_answer($player_id, $question_id, $answer_string, $game){
	if($answer_string != ''){
		$ans_id = get_answer_id($question_id, $answer_string, $game);
		if($ans_id <= 0){
			// answer still must be created
			create_answer($question_id, $answer_string, $game);
			$ans_id = get_answer_id($question_id, $answer_string, $game);
		}
	
		remove_answers_for_question($player_id, $question_id, $game);
		
		$query = "INSERT INTO iwsAnswers(player, answer, game) VALUES (".intval($player_id).", $ans_id, ".intval($game).");";
		$result = mysql_query($query);
		if(!$result){
			echo "insert_answer: Anfrage fehlgeschlagen: " . mysql_error() . "<br/>";
		}
	} else {
		remove_answers_for_question($player_id, $question_id, $game);
	}
}

/*function add_answer_number($player, $round, $question, $answer, $game){

if($answer == "") return;

// find player id
$query = "SELECT id FROM iwsPlayers WHERE name LIKE '" . validate_string_for_mysql_html($player) . "' AND game=".intval($game).";";
$result = mysql_query($query);
if(!$result){
echo "add_answer_number: Anfrage 1 fehlgeschlagen: " . mysql_error() . "<br/>";
}

$player_id = mysql_fetch_array($result, MYSQL_ASSOC);
mysql_free_result($result);

// find question id
$query = "SELECT id FROM iwsQuestion WHERE number = " . validate_string_for_mysql_html($question) . " AND round = " . mysql_real_escape_string($round) . " AND game=".intval($game).";";
$result = mysql_query($query);
if(!$result){
echo "add_answer_number: Anfrage 2 fehlgeschlagen: " . mysql_error() . "<br/>";
}

$question_id = mysql_fetch_array($result, MYSQL_ASSOC);
mysql_free_result($result);

$counter = 0;
do{
// find answer id
$query = "SELECT id FROM iwsAnswer WHERE question = " . mysql_real_escape_string($question_id["id"]) . " AND value LIKE '" . validate_string_for_mysql_html($answer) . "' AND game=".intval($game).";";
$result = mysql_query($query);
if(!$result){
echo "add_answer_number: Anfrage 3 fehlgeschlagen: " . mysql_error() . "<br/>";
}

$answer_id = mysql_fetch_array($result, MYSQL_ASSOC);
mysql_free_result($result);

if($answer_id["id"] == ""){
create_answer_number($question_id["id"], $answer, $game);
}

$counter++;
} while($answer_id["id"] == "" && $counter < 3);

if($counter > 2){
echo "infiniloop!";
}

// remove previous answer(s) of that round
$query = "DELETE FROM iwsAnswers
WHERE player IN (SELECT id FROM iwsPlayers WHERE name LIKE '" . validate_string_for_mysql_html($player) . "')
AND answer IN (SELECT iwsAnswer.id FROM iwsAnswer JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id WHERE iwsQuestion.round = " . mysql_real_escape_string($round) . " AND iwsQuestion.number = " . validate_string_for_mysql_html($question) . ") AND game=".intval($game).";";
$result = mysql_query($query);
if(!$result){
echo "add_answer_number: Anfrage 4 fehlgeschlagen: " . mysql_error() . "<br/>";
}	

// insert the player's answer
$query = "REPLACE INTO iwsAnswers(player, answer, game) VALUES ("
. mysql_real_escape_string($player_id["id"]) . ", "
. mysql_real_escape_string($answer_id["id"]) . ", "
. intval($game).");";

$result = mysql_query($query);
if(!$result){
echo "add_answer_number: Anfrage 5 ". mysql_real_escape_string($answer_id["id"]) ."fehlgeschlagen: " . mysql_error() . "<br/>";
}	
}*/

function get_max_question_of_round($nr, $game){
	$query = "SELECT MAX(number) AS max FROM iwsQuestion WHERE round = " . mysql_real_escape_string($nr) . " AND game=".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "get_max_question_of_round: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	return $answer["max"];
}

function get_no_of_questions($round, $game){
	$query = "SELECT COUNT(id) AS count FROM iwsQuestion WHERE round = " . intval($round) . " AND game=".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "get_no_of_questions: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	return intval($answer["count"]);
}

function get_question_by_id($id){
	$query = "SELECT id, value FROM iwsQuestion WHERE id=".intval($id);
	$result = mysql_query($query);
	if(!$result){
		echo "get_question_by_id: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	if(isset($answer["value"]) and $answer["value"] != ""){
		return $answer["value"];
	} else {
		return "---get_question_by_id(".intval($id).") failed---";
	}
}

function get_answer_by_id($id){
	$query = "SELECT id, value FROM iwsAnswer WHERE id=".intval($id);
	$result = mysql_query($query);
	if(!$result){
		echo "get_answer_by_id: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	if(isset($answer["value"]) and $answer["value"] != ""){
		return $answer["value"];
	} else {
		return "---get_answer_by_id(".intval($id).") failed---";
	}
}

function get_max_round($game){
	$query = "SELECT MAX(round) AS max FROM iwsQuestion WHERE game=".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "get_max_round: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	return $answer["max"];
}

function get_first_player($game){
	$query = "SELECT name FROM iwsPlayers WHERE id IN (SELECT MIN(id) FROM iwsPlayers WHERE game=".intval($game).");";
	$result = mysql_query($query);
	if(!$result){
		echo "get_first_player: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	return $answer["name"];
}

function get_answers_for($question, $game){
	$question = intval($question);
	$query = "SELECT DISTINCT iwsAnswer.value AS answer
		    FROM iwsAnswer JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id 
		    WHERE iwsQuestion.id = ".$question." AND iwsQuestion.game=".intval($game)." AND iwsAnswer.game=".intval($game).";";
	$result = mysql_query($query);
	if(!$result){
		echo "get_answers_for: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$retstring = '';
	while($answer = mysql_fetch_array($result)){
		$retstring .= ',"'.$answer['answer'].'"';
	}
	
	mysql_free_result($result);
	
	return substr($retstring, 1);
}

function add_game($name, $user_id){
	$query = "INSERT INTO iwsGames(name, user) VALUES ('".validate_string_for_mysql_html($name)."', ".intval($user_id).")";
	$result = mysql_query($query);
	if(!$result){
		echo "add_game: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
}

function get_game_by_id($id){
	$query = "SELECT name FROM iwsGames WHERE id=".intval($id);
	$result = mysql_query($query);
	if(!$result){
		echo "get_game_by_id: Anfrage  fehlgeschlagen: " . mysql_error() . "<br/>";
	}
	
	$answer = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	if(isset($answer["name"]) and $answer["name"] != ""){
		return $answer["name"];
	} else {
		return "---get_game_by_id(".intval($id).") failed---";
	}
}

function get_user_for_game($game){
	$query = "SELECT user FROM iwsGames WHERE id=".intval($game);
	$result = mysql_query($query);
	if($answer = mysql_fetch_array($result)){
		$user_id = intval($answer['user']);
	} else {
		$user_id = 0;
	}
	
	mysql_free_result($result);
	
	return $user_id;
}

?>
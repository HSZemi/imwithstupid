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
	$link = mysqli_connect("localhost", USER, PASS)
	or die("Keine Verbindung möglich: " . mysqli_error($link));
// 	echo "Verbindung zum Datenbankserver erfolgreich<br/>";

	mysqli_select_db($link, "iwstest") or die("Auswahl der Datenbank fehlgeschlagen</br>");
	return $link;
}

function db_close($link){
	mysqli_close($link);
}

function validate_string_for_mysqli_html($link, $string){
	return mysqli_real_escape_string($link, htmlspecialchars($string, ENT_QUOTES | ENT_HTML401));
}


function create_player($link, $name, $game){
	$query = "INSERT INTO iwsPlayers(name, game) VALUES ('" . validate_string_for_mysqli_html($link, $name) . "', ".intval($game).");";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "create_player: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
}

function remove_player($link, $name, $game){
	$query = "DELETE FROM iwsPlayers WHERE name LIKE '" . validate_string_for_mysqli_html($link, $name) . "' AND game = ".intval($game).";";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "remove_player: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
}


function create_question($link, $round, $value, $game){
	$query = "INSERT INTO iwsQuestion(round, value, game) VALUES (" 
		. mysqli_real_escape_string($link, $round) . ", '" 
		. validate_string_for_mysqli_html($link, $value) . "', "
		. intval($game).");";
	
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "create_question: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
}

function delete_question($link, $id){
	$query = "DELETE FROM iwsQuestion WHERE id=".intval($id);
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "delete_question: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
}

function create_answer($link, $question_id, $value, $game){
	// find question id
	
	$query = "INSERT INTO iwsAnswer(question, value, game) VALUES ('" 
		. intval($question_id) . "', '"
		. validate_string_for_mysqli_html($link, $value) . "', "
		. intval($game).");";
	
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "create_answer_number: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
}


function delete_answer($link, $id){
	$query = "DELETE FROM iwsAnswer WHERE id=".intval($id);
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "delete_answer: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
}

function get_player_id($link, $player_name, $game){
	$query = "SELECT id FROM iwsPlayers WHERE name LIKE '" .  validate_string_for_mysqli_html($link, $player_name) . "' AND game=".intval($game).";";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "get_player_id: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
		return -1;
	} else {
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$player_id = $row['id'];
		mysqli_free_result($result);
		return $player_id;
	}
}

function get_answer_id($link, $question_id, $answer_string, $game){
	$query = "SELECT id FROM iwsAnswer WHERE question = " . intval($question_id) . " AND value LIKE '" . validate_string_for_mysqli_html($link, $answer_string) . "' AND game=".intval($game).";";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "get_answer_id: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
		return -1;
	} else {
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		mysqli_free_result($result);
		
		if(isset($row['id'])){
			return $row['id'];
		} else {
			return -1;
		}
	}
}

function remove_answers_for_question($link, $player_id, $question_id, $game){
	$query = "DELETE FROM iwsAnswers WHERE player=".intval($player_id)." AND answer IN (SELECT id FROM iwsAnswer WHERE question=".intval($question_id)." AND game=".intval($game).");";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "remove_answers_for_question: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}	
}

function insert_answer($link, $player_id, $question_id, $answer_string, $game){
	if($answer_string != ''){
		$ans_id = get_answer_id($link, $question_id, $answer_string, $game);
		if($ans_id <= 0){
			// answer still must be created
			create_answer($link, $question_id, $answer_string, $game);
			$ans_id = get_answer_id($link, $question_id, $answer_string, $game);
		}
	
		remove_answers_for_question($link, $player_id, $question_id, $game);
		
		$query = "INSERT INTO iwsAnswers(player, answer, game) VALUES (".intval($player_id).", $ans_id, ".intval($game).");";
		$result = mysqli_query($link, $query);
		if(!$result){
			echo "insert_answer: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
		}
	} else {
		remove_answers_for_question($link, $player_id, $question_id, $game);
	}
}

function get_max_question_of_round($link, $nr, $game){
	$query = "SELECT MAX(number) AS max FROM iwsQuestion WHERE round = " . mysqli_real_escape_string($link, $nr) . " AND game=".intval($game).";";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "get_max_question_of_round: Anfrage  fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
	
	$answer = mysqli_fetch_array($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	
	return $answer["max"];
}

function get_no_of_questions($link, $round, $game){
	$query = "SELECT COUNT(id) AS count FROM iwsQuestion WHERE round = " . intval($round) . " AND game=".intval($game).";";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "get_no_of_questions: Anfrage  fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
	
	$answer = mysqli_fetch_array($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	
	return intval($answer["count"]);
}

function get_question_by_id($link, $id){
	$query = "SELECT id, value FROM iwsQuestion WHERE id=".intval($id);
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "get_question_by_id: Anfrage  fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
	
	$answer = mysqli_fetch_array($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	
	if(isset($answer["value"]) and $answer["value"] != ""){
		return $answer["value"];
	} else {
		return "---get_question_by_id(".intval($id).") failed---";
	}
}

function get_answer_by_id($link, $id){
	$query = "SELECT id, value FROM iwsAnswer WHERE id=".intval($id);
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "get_answer_by_id: Anfrage  fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
	
	$answer = mysqli_fetch_array($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	
	if(isset($answer["value"]) and $answer["value"] != ""){
		return $answer["value"];
	} else {
		return "---get_answer_by_id(".intval($id).") failed---";
	}
}

function get_max_round($link, $game){
	$query = "SELECT MAX(round) AS max FROM iwsQuestion WHERE game=".intval($game).";";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "get_max_round: Anfrage  fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
	
	$answer = mysqli_fetch_array($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	
	return $answer["max"];
}

function get_first_player($link, $game){
	$query = "SELECT name FROM iwsPlayers WHERE id IN (SELECT MIN(id) FROM iwsPlayers WHERE game=".intval($game).");";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "get_first_player: Anfrage  fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
	
	$answer = mysqli_fetch_array($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	
	return $answer["name"];
}

function get_answers_for($link, $question, $game){
	$question = intval($question);
	$query = "SELECT DISTINCT iwsAnswer.value AS answer
		    FROM iwsAnswer JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id 
		    WHERE iwsQuestion.id = ".$question." AND iwsQuestion.game=".intval($game)." AND iwsAnswer.game=".intval($game).";";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "get_answers_for: Anfrage  fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
	
	$retstring = '';
	while($answer = mysqli_fetch_array($result)){
		$retstring .= ',"'.$answer['answer'].'"';
	}
	
	mysqli_free_result($result);
	
	return substr($retstring, 1);
}

function add_game($link, $name, $user_id){
	$query = "INSERT INTO iwsGames(name, user) VALUES ('".validate_string_for_mysqli_html($link, $name)."', ".intval($user_id).")";
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "add_game: Anfrage  fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
}

function get_game_by_id($link, $id){
	$query = "SELECT name FROM iwsGames WHERE id=".intval($id);
	$result = mysqli_query($link, $query);
	if(!$result){
		echo "get_game_by_id: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
	}
	
	$answer = mysqli_fetch_array($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	
	if(isset($answer["name"]) and $answer["name"] != ""){
		return $answer["name"];
	} else {
		return "---get_game_by_id(".intval($id).") failed---";
	}
}

function get_user_for_game($link, $game){
	$query = "SELECT user FROM iwsGames WHERE id=".intval($game);
	$result = mysqli_query($link, $query);
	if($answer = mysqli_fetch_array($result)){
		$user_id = intval($answer['user']);
	} else {
		$user_id = 0;
	}
	
	mysqli_free_result($result);
	
	return $user_id;
}

?>
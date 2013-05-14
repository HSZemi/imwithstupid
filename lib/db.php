<?php

function db_connect(){
	// Verbindung aufbauen, auswählen einer Datenbank
	$link = mysql_connect("localhost", "iws", "iws")
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

// HTML stuff

function html_output_round_questions_answers_by_user($round, $user){
	$query = "SELECT iwsQuestion.round AS round,  iwsQuestion.number AS number, iwsQuestion.value as question, answer
		FROM (SELECT iwsAnswer.value AS answer, iwsQuestion.id AS question_id
			FROM ((iwsPlayers JOIN iwsAnswers ON iwsPlayers.id = iwsAnswers.player) 
				JOIN iwsAnswer ON iwsAnswer.id = iwsAnswers.answer) JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id
			WHERE iwsPlayers.name LIKE '" . mysql_real_escape_string($user) . "') AS answeredquestions
			RIGHT OUTER JOIN iwsQuestion ON iwsQuestion.id = answeredquestions.question_id
		WHERE round = " . mysql_real_escape_string($round) . "
		ORDER BY number ASC";
	$result = mysql_query($query) or die("html_output_round_questions_answers: Anfrage fehlgeschlagen: " . mysql_error());
	
	// HTML output
	
	echo "<table border='1'>\n";
	echo "<tr>\n\t<th>Round</th>\n\t<th>Number</th>\n\t<th>Question</th>\n\t<th>Answer</th>\n</tr>\n\n";
	
	$i = 1;
	while($row = mysql_fetch_array($result)){
		$round	= $row['round'];
		$number	= $row['number'];
		$question	= $row['question'];
		$answer	= $row['answer'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $number . "</td>\n\t<td>" . $question . "</td>\n\t<td><input name='answer_" . $i . "' type='text' size='50' maxlength='100' value='" . $answer . "'></td>\n</tr>\n\n";
		
		$i++;
	}
	echo "</table>\n";
	
	mysql_free_result($result);
	
	
}

function html_output_list_of_players($selected_player){
	$query = "SELECT DISTINCT name FROM iwsPlayers ORDER BY id ASC";
	$result = mysql_query($query) or die("html_output_list_of_players: Anfrage fehlgeschlagen: " . mysql_error());
	
	// HTML output
	while($row = mysql_fetch_array($result)){
		$name	= $row['name'];
		
		if(strcmp($name, $selected_player) == 0){
			echo "\t\t\t<option selected='selected'>" . $name . "</option>\n";
		} else {
			echo "\t\t\t<option>" . $name . "</option>\n";
		}
	}
	mysql_free_result($result);
}

function html_output_round_player_points($nr){
	// table with round - player - points
	$query = "SELECT round, player_name, sum(points) AS sum_points
		FROM bigtable JOIN points_per_answer ON points_per_answer.answer_id = bigtable.answer_id
		WHERE round = " . mysql_real_escape_string($nr) . "
		GROUP BY player_id
		ORDER BY sum_points DESC;";
	
	$result = mysql_query($query) or die("html_output_get_round: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table border='1'>\n";
	echo "<tr>\n\t<th>Round</th>\n\t<th>Player</th>\n\t<th>Points</th>\n</tr>\n\n";
	
	while($row = mysql_fetch_array($result)){
		$round	= $row['round'];
		$player_name= $row['player_name'];
		$sum_points	= $row['sum_points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $player_name . "</td>\n\t<td>" . $sum_points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysql_free_result($result);
}

function html_output_sum_of_all_points(){
	// table with round - player - points
	$query = "SELECT player_name, sum(points) AS sum_points
		FROM bigtable JOIN points_per_answer ON points_per_answer.answer_id = bigtable.answer_id
		GROUP BY player_id
		ORDER BY sum_points DESC;";
	
	$result = mysql_query($query) or die("html_output_get_round: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table border='1'>\n";
	echo "<tr>\n\t<th>Player</th>\n\t<th>Points</th>\n</tr>\n\n";
	
	while($row = mysql_fetch_array($result)){
		$player_name= $row['player_name'];
		$sum_points	= $row['sum_points'];
		
		echo "<tr>\n\t<td>" . $player_name . "</td>\n\t<td>" . $sum_points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysql_free_result($result);
}

function html_output_get_round($nr){
	// table with round - question - player - answer - points per Answer
	$query = "SELECT round, question_value AS question, player_name AS player, answer_value AS answer, points
	 FROM bigtable JOIN points_per_answer ON points_per_answer.answer_id = bigtable.answer_id
	 WHERE round = " . mysql_real_escape_string($nr) . "
	 ORDER BY question_id, player_id;";
	 
	 $result = mysql_query($query) or die("html_output_get_round: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table border='1'>\n";
	echo "<tr>\n\t<th>Round</th>\n\t<th>Question</th>\n\t<th>Player</th>\n\t<th>Answer</th>\n\t<th>Points</th>\n</tr>\n\n";
	
	while($row = mysql_fetch_array($result)){
		$round	= $row['round'];
		$question	= $row['question'];
		$player	= $row['player'];
		$answer	= $row['answer'];
		$points	= $row['points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $question . "</td>\n\t<td>" . $player . "</td>\n\t<td>" . $answer . "</td>\n\t<td>" . $points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysql_free_result($result);
}

function html_output_get_round_points($nr){
	// table with round - question - answer - points per Answer
	$query = "SELECT iwsQuestion.round AS round, iwsQuestion.value AS question, iwsAnswer.value AS answer, count(iwsAnswers.player) AS points
	 FROM ((iwsAnswers) 
		JOIN (iwsAnswer JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id) ON iwsAnswers.answer = iwsAnswer.id)
	 WHERE round = " . mysql_real_escape_string($nr) . "
	 GROUP BY round, question, answer
	 ORDER BY iwsQuestion.id;";
	 
	 $result = mysql_query($query) or die("html_output_get_round_points: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table border='1'>\n";
	echo "<tr>\n\t<th>Round</th>\n\t<th>Question</th>\n\t<th>Answer</th>\n\t<th>Points</th>\n</tr>\n\n";
	
	while($row = mysql_fetch_array($result)){
		$i = 1;
		$round	= $row['round'];
		$question	= $row['question'];
		$answer	= $row['answer'];
		$points	= $row['points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $question . "</td>\n\t<td>" .  $answer . "</td>\n\t<td>" . $points . "</td>\n</tr>\n\n";
	}

	echo "</table>\n";
	
	mysql_free_result($result);
}

function html_output_get_all_rounds(){
	// table with round - question - player - answer - points per Answer
	$query = "SELECT round, question_value AS question, player_name AS player, answer_value AS answer, points
	 FROM bigtable JOIN points_per_answer ON points_per_answer.answer_id = bigtable.answer_id
	 ORDER BY round, question_id;";
	 
	 $result = mysql_query($query) or die("html_output_get_all_rounds: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table border='1'>\n";
	echo "<tr>\n\t<th>Round</th>\n\t<th>Question</th>\n\t<th>Player</th>\n\t<th>Answer</th>\n\t<th>Points</th>\n</tr>\n\n";
	
	while($row = mysql_fetch_array($result)){
		$round	= $row['round'];
		$question	= $row['question'];
		$player	= $row['player'];
		$answer	= $row['answer'];
		$points	= $row['points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $question . "</td>\n\t<td>" . $player . "</td>\n\t<td>" . $answer . "</td>\n\t<td>" . $points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysql_free_result($result);
}

?>
<?php

/*
 * The methods defined in this file will generate some output 
 * html for the management such as lists of players, or
 * standings tables on the other hand.
 */


/* 
 * These statements could also be generated as views, but some hosters
 * won't allow the creation of those. Thus, for easier handling, we
 * have them as constants.
 */
function BIGTABLE($game){
	return "(SELECT iwsGames.id AS game_id, iwsGames.name AS game_name, iwsPlayers.id AS player_id, iwsPlayers.name AS player_name,
			iwsAnswers.id AS answers_id,
			iwsAnswer.id AS answer_id, iwsAnswer.value AS answer_value,
			iwsQuestion.id AS question_id, iwsQuestion.round AS round, iwsQuestion.number AS question_number, iwsQuestion.value AS question_value
		FROM (iwsGames JOIN ((iwsAnswers JOIN iwsPlayers ON iwsAnswers.player = iwsPlayers.id) 
			JOIN (iwsAnswer JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id) ON iwsAnswers.answer = iwsAnswer.id) 
			ON iwsGames.id = iwsQuestion.game)) AS bigtable ";
}
		  
function POINTS_PER_ANSWER($game) { 
	return "(SELECT iwsAnswers.answer AS answer_id, COUNT(iwsAnswers.player) AS points
		FROM iwsAnswers
		WHERE iwsAnswers.game = $game
		GROUP BY iwsAnswers.answer) AS points_per_answer";
}

// table with round - question - answer - points for Answer
function html_output_round_answers_points($nr, $game){
	$query = "SELECT DISTINCT round, question_id AS question, answer_value AS answer, points
	 FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . "
		 ON points_per_answer.answer_id = bigtable.answer_id
	 WHERE round = " . mysql_real_escape_string($nr) . " 
	 ORDER BY question_id, player_id;";
	 
	 $result = mysql_query($query) or die("html_output_get_round: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Frage</th>\n\t<th>Antwort</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
	while($row = mysql_fetch_array($result)){
		$round	= $row['round'];
		$question	= $row['question'];
		$answer	= $row['answer'];
		$points	= $row['points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $question . "</td>\n\t<td>" . $answer . "</td>\n\t<td>" . $points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysql_free_result($result);
}

// for 1 user: table with round - number of question - question - answer (as input field)
function html_output_round_questions_answers_by_user($round, $user, $game){
	$query = "SELECT iwsQuestion.round AS round,  iwsQuestion.number AS number, iwsQuestion.value as question, answer
		FROM ((SELECT iwsAnswer.value AS answer, iwsQuestion.id AS question_id
			FROM ((iwsPlayers JOIN iwsAnswers ON iwsPlayers.id = iwsAnswers.player) 
				JOIN iwsAnswer ON iwsAnswer.id = iwsAnswers.answer) JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id
			WHERE iwsPlayers.name LIKE '" . mysql_real_escape_string($user) . "') AS answeredquestions
			RIGHT OUTER JOIN iwsQuestion ON iwsQuestion.id = answeredquestions.question_id)
		WHERE round = " . mysql_real_escape_string($round) . " AND iwsQuestion.game = ".mysql_real_escape_string($game)." 
		ORDER BY number ASC";
	$result = mysql_query($query) or die("html_output_round_questions_answers: Anfrage fehlgeschlagen: " . mysql_error());
	
	// HTML output
	
	echo "<table class='table table-bordered'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Number</th>\n\t<th>Frage</th>\n\t<th>Antwort</th>\n</tr>\n\n";
	
	$i = 1;
	while($row = mysql_fetch_array($result)){
		$round	= $row['round'];
		$number	= $row['number'];
		$question	= $row['question'];
		$answer	= $row['answer'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $number . "</td>\n\t<td>" . $question . '</td>
		<td><input name="answer_' . $i . '" type="text" size="50" maxlength="100" style="width:95%; margin-bottom: 0px;"; autocomplete="off" data-provide="typeahead" data-source='."'".'['.get_answers_for($round, $number, $game).']'."'".' value="' . $answer . '" /></td>
		</tr>
		
		';
		
		$i++;
	}
	echo "</table>\n";
	
	mysql_free_result($result);
	
	
}

// option list for dropdown/selection consisting of all players' names
function html_output_list_of_players($selected_player, $game){
	$query = "SELECT DISTINCT name FROM iwsPlayers WHERE iwsPlayers.game = ".mysql_real_escape_string($game)." ORDER BY iwsPlayers.id ASC";
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

// table with round - player - points for each player in that round
function html_output_round_player_points($nr, $game){
	$query = "SELECT round, player_name, sum(points) AS sum_points
		FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . "
			ON points_per_answer.answer_id = bigtable.answer_id
		WHERE round = " . mysql_real_escape_string($nr) . " AND game_id = ".mysql_real_escape_string($game)." 
		GROUP BY player_id
		ORDER BY sum_points DESC;";
	
	$result = mysql_query($query) or die("html_output_round_player_points: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Spieler</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
	while($row = mysql_fetch_array($result)){
		$round	= $row['round'];
		$player_name= $row['player_name'];
		$sum_points	= $row['sum_points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $player_name . "</td>\n\t<td>" . $sum_points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysql_free_result($result);
}

// table with player - sum of all points
function html_output_sum_of_all_points($game){
	$query = "SELECT player_name, sum(points) AS sum_points
		FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . " 
			ON points_per_answer.answer_id = bigtable.answer_id
		WHERE game_id = ".mysql_real_escape_string($game)." 
		GROUP BY player_id
		ORDER BY sum_points DESC;";
	
	$result = mysql_query($query) or die("html_output_sum_of_all_points: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Spieler</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
	while($row = mysql_fetch_array($result)){
		$player_name= $row['player_name'];
		$sum_points	= $row['sum_points'];
		
		echo "<tr>\n\t<td>" . $player_name . "</td>\n\t<td>" . $sum_points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysql_free_result($result);
}

// table with round - question - player - answer - points per answer
function html_output_get_round($nr, $game){
	$query = "SELECT round, question_value AS question, player_name AS player, answer_value AS answer, points
	 FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . " ON points_per_answer.answer_id = bigtable.answer_id
	 WHERE round = " . mysql_real_escape_string($nr) . " AND game_id = ".mysql_real_escape_string($game)." 
	 ORDER BY question_id, player_id;";
	 
	 $result = mysql_query($query) or die("html_output_get_round: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Frage</th>\n\t<th>Spieler</th>\n\t<th>Antwort</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
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

// table with round - question - answer - points per answer
function html_output_get_round_points($nr, $game){
	$query = "SELECT iwsQuestion.round AS round, iwsQuestion.value AS question, iwsAnswer.value AS answer, count(iwsAnswers.player) AS points
	 FROM ((iwsAnswers) 
		JOIN (iwsAnswer JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id) ON iwsAnswers.answer = iwsAnswer.id)
	 WHERE round = " . mysql_real_escape_string($nr) . " AND iwsQuestion.game = ".mysql_real_escape_string($game)." 
	 GROUP BY round, question, answer
	 ORDER BY iwsQuestion.id;";
	 
	 $result = mysql_query($query) or die("html_output_get_round_points: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Frage</th>\n\t<th>Antwort</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
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

// quite a big table with round - question - player - answer - points per Answer
function html_output_get_all_rounds($game){
	$query = "SELECT round, question_value AS question, player_name AS player, answer_value AS answer, points
	 FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . " ON points_per_answer.answer_id = bigtable.answer_id
	 WHERE game_id = ".mysql_real_escape_string($game)." 
	 ORDER BY round, question_id;";
	 
	 $result = mysql_query($query) or die("html_output_get_all_rounds: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Frage</th>\n\t<th>Spieler</th>\n\t<th>Antwort</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
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

function html_list_questions_of_round($round, $game){
	$query = "SELECT value AS question
	 FROM iwsQuestion
	 WHERE round = " . mysql_real_escape_string($round) . " AND iwsQuestion.game = ".mysql_real_escape_string($game)." 
	 ORDER BY number ASC;";
	 
	 $result = mysql_query($query) or die("html_list_questions_of_round: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<ol>\n";
	
	while($row = mysql_fetch_array($result)){
		$question	= $row['question'];
		
		echo "<li>".$question."</li>\n";
	}
	echo "</ol>\n";
	
	mysql_free_result($result);
}

function html_bbcode_results_current_round($round, $game){
	$query = "SELECT player_name, sum(points) AS sum_points
		FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . "
			ON points_per_answer.answer_id = bigtable.answer_id
		WHERE round = " . mysql_real_escape_string($round) . " AND game_id = ".mysql_real_escape_string($game)." 
		GROUP BY player_id
		ORDER BY sum_points DESC;";
	
	$result = mysql_query($query) or die("html_bbcode_results_current_round: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<textarea id='text_results_round' class='span3' rows='20'>\n";
	
	if($row = mysql_fetch_array($result)){
		$sum_points	= $row['sum_points'];
		$rank = 1;
		$pos = 1;
		$prev_pts = $sum_points;
	
		do{
			$player_name= $row['player_name'];
			$sum_points	= $row['sum_points'];
			
			if($sum_points < $prev_pts){
				$rank=$pos;
			}
			$pos++;
			
			echo $rank . '. ' . $player_name . " - " . $sum_points . "\n";
			
			$prev_pts = $sum_points;
		}while($row = mysql_fetch_array($result));
	}
	echo "</textarea>\n";
	
	mysql_free_result($result);

}

function html_bbcode_results($game){
	$query = "SELECT player_name, sum(points) AS sum_points
		FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . "
			ON points_per_answer.answer_id = bigtable.answer_id
		WHERE game_id = ".mysql_real_escape_string($game)." 
		GROUP BY player_id
		ORDER BY sum_points DESC;";
	
	$result = mysql_query($query) or die("html_bbcode_results: Anfrage fehlgeschlagen: " . mysql_error());
	 
	// HTML output
	
	echo "<textarea id='text_results_all' class='span3' rows='20'>\n";
	
	if($row = mysql_fetch_array($result)){
		$sum_points	= $row['sum_points'];
		$rank = 1;
		$pos = 1;
		$prev_pts = $sum_points;
	
		do{
			$player_name= $row['player_name'];
			$sum_points	= $row['sum_points'];
			
			if($sum_points < $prev_pts){
				$rank=$pos;
			}
			$pos++;
			
			echo $rank . '. ' . $player_name . " - " . $sum_points . "\n";
			
			$prev_pts = $sum_points;
		}while($row = mysql_fetch_array($result));
	}
	echo "</textarea>\n";
	
	mysql_free_result($result);

}

function html_list_of_games(){
	echo '<ol>';
	
	$query = "SELECT id, name FROM iwsGames";
	$result = mysql_query($query) or die("html_list_of_games: Anfrage fehlgeschlagen: " . mysql_error());
	
	while($row = mysql_fetch_array($result)){
		echo "<li><a href='iws.php?id=".$row['id']."'>".$row['name']."</a></li>\n";
	}
	
	echo '</ol>';
}

?>
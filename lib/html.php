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
			iwsQuestion.id AS question_id, iwsQuestion.round AS round, iwsQuestion.value AS question_value
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
function html_table_round_answers_points($link, $nr, $game){
	$query = "SELECT DISTINCT round, question_id, player_id AS question, answer_value AS answer, points
	 FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . "
		 ON points_per_answer.answer_id = bigtable.answer_id
	 WHERE round = " . mysqli_real_escape_string($link, $nr) . " 
	 ORDER BY question_id, player_id;";
	 
	 $result = mysqli_query($link, $query) or die("html_table_round_answers_points: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Frage</th>\n\t<th>Antwort</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
	while($row = mysqli_fetch_array($result)){
		$round	= $row['round'];
		$question	= $row['question'];
		$answer	= $row['answer'];
		$points	= $row['points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $question . "</td>\n\t<td>" . $answer . "</td>\n\t<td>" . $points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysqli_free_result($result);
}

// for 1 user: table with round - number of question - question - answer (as input field)
function html_table_round_questions_answers_by_player($link, $round, $user, $game){
	$query = "SELECT iwsQuestion.round AS round, iwsQuestion.id AS question_id, iwsQuestion.value as question, answer
		FROM ((SELECT iwsAnswer.value AS answer, iwsQuestion.id AS question_id
			FROM ((iwsPlayers JOIN iwsAnswers ON iwsPlayers.id = iwsAnswers.player) 
				JOIN iwsAnswer ON iwsAnswer.id = iwsAnswers.answer) JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id
			WHERE iwsPlayers.name LIKE '" . mysqli_real_escape_string($link, $user) . "') AS answeredquestions
			RIGHT OUTER JOIN iwsQuestion ON iwsQuestion.id = answeredquestions.question_id)
		WHERE round = " . mysqli_real_escape_string($link, $round) . " AND iwsQuestion.game = ".mysqli_real_escape_string($link, $game)." 
		ORDER BY iwsQuestion.id ASC";
	$result = mysqli_query($link, $query) or die("html_output_round_questions_answers: Anfrage fehlgeschlagen: " . mysqli_error($link));
	
	// HTML output
	
	echo "<table class='table table-bordered table-hover'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Nummer</th>\n\t<th>Frage</th>\n\t<th>Antwort</th>\n</tr>\n\n";
	
	$number = 1;
	while($row = mysqli_fetch_array($result)){
		$round		= $row['round'];
		$question_id 	= $row['question_id'];
		//$number		= $row['number'];
		$question		= $row['question'];
		$answer		= $row['answer'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $number . "</td>\n\t<td>" . $question . '</td>
		<td><input name="answer_' . $question_id . '" type="text" size="50" maxlength="100" style="width:95%; margin-bottom: 0px;"; autocomplete="off" data-provide="typeahead" data-source='."'".'['.get_answers_for($link, $question_id, $game).']'."'".' value="' . $answer . '" /></td>
		</tr>
		
		';
		
		$number++;
	}
	echo "</table>\n";
	
	mysqli_free_result($result);
	
	
}

// option list for dropdown/selection consisting of all players' names
function html_dropdown_list_of_players($link, $selected_player, $game, $select_id = ""){
	$query = "SELECT DISTINCT name, id FROM iwsPlayers WHERE iwsPlayers.game = ".mysqli_real_escape_string($link, $game)." ORDER BY iwsPlayers.id ASC";
	$result = mysqli_query($link, $query) or die("html_output_list_of_players: Anfrage fehlgeschlagen: " . mysqli_error($link));
	
	// HTML output
	echo '<select name="player_name" id="'.$select_id.'" size="1">';
	while($row = mysqli_fetch_array($result)){
		$name	= $row['name'];
		
		if(strcmp($name, $selected_player) == 0){
			echo "\t\t\t<option selected='selected'>" . $name . "</option>\n";
		} else {
			echo "\t\t\t<option>" . $name . "</option>\n";
		}
	}
	echo '</select>';
	mysqli_free_result($result);
}

// table with round - player - points for each player in that round
function html_table_round_player_points($link, $nr, $game){
	$query = "SELECT round, player_name, sum(points) AS sum_points
		FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . "
			ON points_per_answer.answer_id = bigtable.answer_id
		WHERE round = " . mysqli_real_escape_string($link, $nr) . " AND game_id = ".mysqli_real_escape_string($link, $game)." 
		GROUP BY player_id
		ORDER BY sum_points DESC;";
	
	$result = mysqli_query($link, $query) or die("html_output_round_player_points: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Spieler</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
	while($row = mysqli_fetch_array($result)){
		$round	= $row['round'];
		$player_name= $row['player_name'];
		$sum_points	= $row['sum_points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $player_name . "</td>\n\t<td>" . $sum_points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysqli_free_result($result);
}

// table with player - sum of all points
function html_table_sum_of_all_points($link, $game){
	$query = "SELECT player_name, sum(points) AS sum_points
		FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . " 
			ON points_per_answer.answer_id = bigtable.answer_id
		WHERE game_id = ".mysqli_real_escape_string($link, $game)." 
		GROUP BY player_id
		ORDER BY sum_points DESC;";
	
	$result = mysqli_query($link, $query) or die("html_output_sum_of_all_points: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Spieler</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
	while($row = mysqli_fetch_array($result)){
		$player_name= $row['player_name'];
		$sum_points	= $row['sum_points'];
		
		echo "<tr>\n\t<td>" . $player_name . "</td>\n\t<td>" . $sum_points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysqli_free_result($result);
}

// table with round - question - player - answer - points per answer
function html_table_get_round($link, $nr, $game){
	$query = "SELECT round, question_value AS question, player_name AS player, answer_value AS answer, points
	 FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . " ON points_per_answer.answer_id = bigtable.answer_id
	 WHERE round = " . mysqli_real_escape_string($link, $nr) . " AND game_id = ".mysqli_real_escape_string($link, $game)." 
	 ORDER BY question_id, player_id;";
	 
	 $result = mysqli_query($link, $query) or die("html_table_get_round: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Frage</th>\n\t<th>Spieler</th>\n\t<th>Antwort</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
	while($row = mysqli_fetch_array($result)){
		$round	= $row['round'];
		$question	= $row['question'];
		$player	= $row['player'];
		$answer	= $row['answer'];
		$points	= $row['points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $question . "</td>\n\t<td>" . $player . "</td>\n\t<td>" . $answer . "</td>\n\t<td>" . $points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysqli_free_result($result);
}

// table with round - question - answer - points per answer
function html_table_get_round_points($link, $nr, $game){
	$query = "SELECT iwsQuestion.round AS round, iwsQuestion.value AS question, iwsAnswer.value AS answer, count(iwsAnswers.player) AS points
	 FROM ((iwsAnswers) 
		JOIN (iwsAnswer JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id) ON iwsAnswers.answer = iwsAnswer.id)
	 WHERE round = " . mysqli_real_escape_string($link, $nr) . " AND iwsQuestion.game = ".mysqli_real_escape_string($link, $game)." 
	 GROUP BY round, question, answer
	 ORDER BY iwsQuestion.id;";
	 
	 $result = mysqli_query($link, $query) or die("html_table_get_round_points: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Frage</th>\n\t<th>Antwort</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
	while($row = mysqli_fetch_array($result)){
		$i = 1;
		$round	= $row['round'];
		$question	= $row['question'];
		$answer	= $row['answer'];
		$points	= $row['points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $question . "</td>\n\t<td>" .  $answer . "</td>\n\t<td>" . $points . "</td>\n</tr>\n\n";
	}

	echo "</table>\n";
	
	mysqli_free_result($result);
}

// quite a big table with round - question - player - answer - points per Answer
function html_table_get_all_rounds($link, $game){
	$query = "SELECT round, question_value AS question, player_name AS player, answer_value AS answer, points
	 FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . " ON points_per_answer.answer_id = bigtable.answer_id
	 WHERE game_id = ".mysqli_real_escape_string($link, $game)." 
	 ORDER BY round, question_id;";
	 
	 $result = mysqli_query($link, $query) or die("html_output_get_all_rounds: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo "<table class='resulttable'>\n";
	echo "<tr>\n\t<th>Runde</th>\n\t<th>Frage</th>\n\t<th>Spieler</th>\n\t<th>Antwort</th>\n\t<th>Punkte</th>\n</tr>\n\n";
	
	while($row = mysqli_fetch_array($result)){
		$round	= $row['round'];
		$question	= $row['question'];
		$player	= $row['player'];
		$answer	= $row['answer'];
		$points	= $row['points'];
		
		echo "<tr>\n\t<td>" . $round . "</td>\n\t<td>" . $question . "</td>\n\t<td>" . $player . "</td>\n\t<td>" . $answer . "</td>\n\t<td>" . $points . "</td>\n</tr>\n\n";
	}
	echo "</table>\n";
	
	mysqli_free_result($result);
}

function html_list_questions_with_answers($link, $round, $game){
	$query = "SELECT id, value 
	 FROM iwsQuestion
	 WHERE round = " . mysqli_real_escape_string($link, $round) . " AND iwsQuestion.game = ".mysqli_real_escape_string($link, $game)." 
	 ORDER BY id ASC;";
	 
	 $result = mysqli_query($link, $query) or die("html_list_answers_of_round: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo '<form action="deleteanswer.php" name="delete_answer" method="post">';
	echo "<ol>\n";
	
	while($row = mysqli_fetch_array($result)){
		$id		= $row['id'];
		$value	= $row['value'];
		
		echo "<li>".$value."<a class='btn btn-link' href='deletequestion.php?id=$id' title='Frage $id löschen'><i class='icon-trash'></i></a></li>";
		html_list_answers_for_question($link, intval($id));
	}
	echo "</ol>\n";
	echo "</form>";
	
	mysqli_free_result($result);
}

function html_list_questions_of_round($link, $round, $game){
	$query = "SELECT id, value AS question
	 FROM iwsQuestion
	 WHERE round = " . mysqli_real_escape_string($link, $round) . " AND iwsQuestion.game = ".mysqli_real_escape_string($link, $game)." 
	 ORDER BY id ASC;";
	 
	 $result = mysqli_query($link, $query) or die("html_list_questions_of_round: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo '<form action="deletequestion.php" name="delete_question" method="post">';
	echo "<ol>\n";
	
	while($row = mysqli_fetch_array($result)){
		$id		= $row['id'];
		$question	= $row['question'];
		
		echo "<li>".$question." <button class='btn btn-link' type='submit' value='$id' name='question_to_delete'><i class='icon-trash'></i></button></li>\n";
	}
	echo "</ol>\n";
	echo "</form>";
	
	mysqli_free_result($result);
}

function html_list_answers_of_round($link, $round, $game){
	$query = "SELECT id, value 
	 FROM iwsQuestion
	 WHERE round = " . mysqli_real_escape_string($link, $round) . " AND iwsQuestion.game = ".mysqli_real_escape_string($link, $game)." 
	 ORDER BY id ASC;";
	 
	 $result = mysqli_query($link, $query) or die("html_list_answers_of_round: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo '<form action="deleteanswer.php" name="delete_answer" method="post">';
	echo "<ol>\n";
	
	while($row = mysqli_fetch_array($result)){
		$id		= $row['id'];
		$value	= $row['value'];
		
		echo "<li>".$value."</li>";
		html_list_answers_for_question($link, intval($id));
	}
	echo "</ol>\n";
	echo "</form>";
	
	mysqli_free_result($result);
}

function html_list_answers_for_question($link, $question_id){
	$query = "SELECT id, value AS answer
	 FROM iwsAnswer
	 WHERE question = " . intval($question_id)." 
	 ORDER BY id ASC;";
	 
	 $result = mysqli_query($link, $query) or die("html_list_answers_for_question: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo "<ul>\n";
	
	while($row = mysqli_fetch_array($result)){
		$id		= $row['id'];
		$answer	= $row['answer'];
		
		echo "<li>".$answer." <button class='btn btn-link' type='submit' value='$id' name='answer_to_delete'><i class='icon-remove'></i></button></li>\n";
	}
	echo "</ul>\n";
	
	mysqli_free_result($result);
}

function html_bbcode_results_current_round($link, $round, $game){
	$query = "SELECT player_name, sum(points) AS sum_points
		FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . "
			ON points_per_answer.answer_id = bigtable.answer_id
		WHERE round = " . mysqli_real_escape_string($link, $round) . " AND game_id = ".mysqli_real_escape_string($link, $game)." 
		GROUP BY player_id
		ORDER BY sum_points DESC;";
	
	$result = mysqli_query($link, $query) or die("html_bbcode_results_current_round: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo "<textarea id='text_results_round' class='span3' rows='20'>\n";
	
	if($row = mysqli_fetch_array($result)){
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
		}while($row = mysqli_fetch_array($result));
	}
	echo "</textarea>\n";
	
	mysqli_free_result($result);

}

function html_bbcode_round_answers_points($link, $round, $game){
	$text = "";
	$query = "SELECT id, value FROM iwsQuestion WHERE round=".intval($round)." AND game=".intval($game)." ORDER BY id ASC";
	$result = mysqli_query($link, $query) or die("html_bbcode_round_answers_points 1: Anfrage fehlgeschlagen: " . mysqli_error($link));
	
	$q_nr = 1;
	while($row = mysqli_fetch_array($result)){
		$text .= "[b] $q_nr. ".$row['value']."[/b]\n";
		$question_id = intval($row['id']);
		
		$query2 = "SELECT value AS answer, points_per_answer.points AS points FROM ".POINTS_PER_ANSWER($game)." JOIN iwsAnswer ON points_per_answer.answer_id = iwsAnswer.id WHERE iwsAnswer.question = $question_id AND points > 0 ORDER BY points DESC";
		$result2 = mysqli_query($link, $query2) or die("html_bbcode_round_answers_points 2: Anfrage fehlgeschlagen: " . mysqli_error($link));
		
		while($row2 = mysqli_fetch_array($result2)){
			$answer = $row2['answer'];
			$points = ($row2['points']);
			
			$text .= "$answer $points\n";
		}
		
		mysqli_free_result($result2);
		
		$text .= "\n";
		$q_nr++;
		
	}
	
	mysqli_free_result($result);

	// HTML output
	
	echo "<textarea id='text_points_answers_round' class='span4' rows='50'>\n";
	echo "$text\n";
	echo "</textarea>\n";
	

}

function html_bbcode_results($link, $game){
	$query = "SELECT player_name, sum(points) AS sum_points
		FROM " . BIGTABLE($game) . " JOIN " . POINTS_PER_ANSWER($game) . "
			ON points_per_answer.answer_id = bigtable.answer_id
		WHERE game_id = ".mysqli_real_escape_string($link, $game)." 
		GROUP BY player_id
		ORDER BY sum_points DESC;";
	
	$result = mysqli_query($link, $query) or die("html_bbcode_results: Anfrage fehlgeschlagen: " . mysqli_error($link));
	 
	// HTML output
	
	echo "<textarea id='text_results_all' class='span3' rows='20'>\n";
	
	if($row = mysqli_fetch_array($result)){
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
		}while($row = mysqli_fetch_array($result));
	}
	echo "</textarea>\n";
	
	mysqli_free_result($result);

}

function html_list_of_games($link, $user_id){
	echo '<ol>';
	
	$query = "SELECT id, name FROM iwsGames WHERE user=".intval($user_id);
	$result = mysqli_query($link, $query) or die("html_list_of_games: Anfrage fehlgeschlagen: " . mysqli_error($link));
	
	while($row = mysqli_fetch_array($result)){
		echo "<li><a href='iws.php?id=".$row['id']."'>".$row['name']."</a></li>\n";
	}
	mysqli_free_result($result);
	
	echo '</ol>';
}

function html_userlist($link){
	echo "<ul>\n";
	$query = "SELECT id, username FROM iwsUsers;";
	$result = mysqli_query($link, $query) or die("html_userlist: Anfrage fehlgeschlagen: " . mysqli_error($link));
	
	while($row = mysqli_fetch_array($result)){
		echo "<li>".$row['username']." (".$row['id'].") <button type='submit' name='delete_user' value='".$row['id']."'><i class='icon-trash'></i></button></li>\n";
	}
	mysqli_free_result($result);
	
	echo "</ul>\n";
}

?>
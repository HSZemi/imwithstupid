 <?php
 
function post_action(){
isset($_POST["action"]) ? $action = $_POST["action"] : $action = 'load_user_round';
isset($_POST["round"]) ? $_SESSION['round'] = $_POST["round"] : '';
isset($_POST["player"]) ? $_SESSION['player'] = $_POST["player"] : '';
isset($_POST["active_tab"]) ? $_SESSION['activetab'] = $_POST["active_tab"] : '';

if(isset($_POST["active_tab"]) && $_SESSION['activetab'] === "#enter_results" && !isset($_POST["action"])){
	$action = "load_user_round";
}


/*if($round == ""){
	$round = get_max_round($game);
}*/
$round = $_SESSION['round'];
$game = $_SESSION['game_id'];
$player = $_SESSION['player'];

if($player == ""){
	$player = get_first_player($game);
}

$message = '';

switch ($action) {
	case "add_player":
		$name = $_POST["name"];
		if($name != ""){
			create_player($name, $game);
			$message[0] = 'alert alert-success';
			$message[1] = "Spieler " . $name . " hinzugefügt.";
		} else {
			$message[0] = 'alert alert-error';
			$message[1] = "Ungültiger Spielername.";
		}
		break;
		
	case "remove_player":
		$name = $_POST["name"];
		if($name != ""){
			remove_player($name, $game);
			$message[0] = 'alert';
			$message[1] = "Spieler " . $name . " entfernt.";
		} else {
			$message[0] = 'alert alert-error';
			$message[1] = "Ungültiger Spielername.";
		}
		break;
	case "load_round":
		$message = "Runde " . $round . " wurde geladen.";
		break;
	case "new_round":
		$_SESSION['round'] = max(get_max_round($game), $_SESSION['round'])+1;
		$message[0] = 'alert alert-success';
		$message[1] = "Runde " . $_SESSION['round'] . " wurde angelegt.";
		//create_round($round, $game);
		break;
	case "load_user_round":
		if(isset($_POST["name"])){
			$_SESSION['player'] = $_POST["name"];
		}
		break;
	case "save_user_round":
		$_SESSION['player'] = $_POST["name"];
		
		for($i = 1; $i <= get_max_question_of_round($round,$game); $i++){
			add_answer_number($_SESSION['player'], $round, $i, $_POST["answer_".$i], $game);
		}
		
		$message[0] = 'alert alert-success';
		$message[1] = "Antworten gespeichert.";
		
		break;
	case "add_question":
		$questiontext = $_POST["questiontext"];
		$number = (get_max_question_of_round($round, $game)+1);
		
		create_question($round, $number, $questiontext, $game);
		break;
}

return $message;
}
?>
 <?php
 
include 'db.php';
include 'html.php';
isset($_POST["action"]) ? $action = $_POST["action"] : $action = '';
isset($_POST["round"]) ? $round = $_POST["round"] : $round = 0;
isset($_POST["player"]) ? $player = $_POST["player"] : $player = '';
isset($_POST["active_tab"]) ? $activetab = $_POST["active_tab"] : $activetab = "#enter_results";

if(isset($_POST["active_tab"]) && $activetab === "#enter_results" && !isset($_POST["action"])){
	$action = "load_user_round";
}

$link = db_connect();

if($round == ""){
	$round = get_max_round();
}

if($player == ""){
	$player = get_first_player();
}

switch ($action) {
	case "add_player":
		$name = $_POST["name"];
		if($name != ""){
			create_player($name);
			echo "Spieler " . $name . " hinzugefügt.";
		} else {
			echo "Ungültiger Spielername.";
		}
		break;
		
	case "remove_player":
		$name = $_POST["name"];
		if($name != ""){
			remove_player($name);
			echo "Spieler " . $name . " entfernt.";
		} else {
			echo "Ungültiger Spielername.";
		}
		break;
	case "load_round":
		echo "Runde " . $round . " wurde geladen.";
		break;
	case "new_round":
		$round = get_max_round()+1;
		echo "Runde " . $round . " wurde angelegt.";
		create_round($round);
		break;
	case "load_user_round":
		$player = $_POST["name"];
		break;
	case "save_user_round":
		$player = $_POST["name"];
		
		for($i = 1; $i <= get_max_question_of_round($round); $i++){
			add_answer_number($player, $round, $i, $_POST["answer_".$i]);
		}
		break;
	case "add_question":
		$questiontext = $_POST["questiontext"];
		$number = (get_max_question_of_round($round)+1);
		
		create_question($round, $number, $questiontext);
		break;
}

db_close($link);
?>
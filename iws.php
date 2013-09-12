<?php
    session_start();
    
    include 'lib/db.php';
    include 'lib/html.php';
    include 'lib/action.php';
    
    $link = db_connect();
    
	/* Error if not logged in */
    if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] < 0){
        header("Location: login.php");
    }
    
	/* Select game if GET variable is set */
    if(isset($_GET['id'])){
	$_SESSION['game_id'] = $_GET['id'];
	$_SESSION['round'] = get_max_round($_SESSION['game_id']);
	$_SESSION['player'] = get_first_player($_SESSION['game_id']);
    }

	/* Error if no game selected */
    if(!isset($_SESSION['game_id'])){
	header("Location: index.php");
    } else {
	$game = $_SESSION['game_id'];
    }
    
	/* Error if user is not owner of selected game */
    if(get_user_for_game($game) != $_SESSION['user_id']){
	header("Location: index.php?err=1&user=".get_user_for_game($game)."&gameuser=".$_SESSION['user_id']);
    }

	/* Default round: 1 */
    if(!isset($_SESSION['round'])){
	$_SESSION['round'] = 1;
    }
    

	/* Form handling */
    isset($_POST["active_tab"]) ? $_SESSION['activetab'] = $_POST["active_tab"] : '';
    
    isset($_POST["action"]) ? $action = $_POST["action"] : $action = '';
    
    $message = '';

    switch ($action) {
	case "load_round":
		$_SESSION['round'] = $_POST["round_to_load"];
		$message[0] = 'alert alert-success';
		$message[1] = "Runde " . $_SESSION['round'] . " wurde geladen.";
		break;
	case "start_new_round":
		$_SESSION['round'] = max(get_max_round($game), $_SESSION['round'])+1;
		$message[0] = 'alert alert-success';
		$message[1] = "Runde " . $_SESSION['round'] . " wurde angelegt.";
		break;
		
	case "add_player":
		$name = $_POST["player_name"];
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
		$name = $_POST["player_name"];
		if($name != ""){
			remove_player($name, $game);
			$message[0] = 'alert';
			$message[1] = "Spieler " . $name . " entfernt.";
		} else {
			$message[0] = 'alert alert-error';
			$message[1] = "Ungültiger Spielername.";
		}
		break;
		
	case "load_answers_for_user_round":
		if(isset($_POST["player_name"])){
			$_SESSION['player'] = $_POST["player_name"];
			//$message[0] = 'alert alert-success';
			//$message[1] = "Antworten geladen.";
		}
		break;
	case "save_answers_for_user_round":
		$_SESSION['player'] = $_POST["player_name"];
		$_SESSION['player_id'] = get_player_id($_SESSION['player'], $_SESSION['game_id']);

		foreach($_POST as $key => $value){
			if(substr($key, 0, 7) === 'answer_'){
				$question_id = intval(substr($key, 7));
				$answer = $value;
				insert_answer($_SESSION['player_id'], $question_id, $answer, $_SESSION['game_id']);
			}
		}
		$message[0] = 'alert alert-success';
		$message[1] = "Antworten gespeichert.";
		break;
	
	case "delete_question":
		delete_question(intval($_POST['question_to_delete']));
		$message[0] = 'alert';
		$message[1] = "Frage ".intval($_POST['question_to_delete'])." gelöscht.";
		break;
	case "delete_answer":
		delete_answer(intval($_POST['answer_to_delete']));
		$message[0] = 'alert';
		$message[1] = "Antwort ".intval($_POST['answer_to_delete'])." gelöscht.";
		break;
	case "add_question":
		$questiontext = $_POST["questiontext"];
		$number = (get_no_of_questions($_SESSION['round'], $game)+1);
		
		create_question($_SESSION['round'], $questiontext, $game);
		break;
    }

	/* Default player: first player */
    if(isset($_SESSION['player']) and $_SESSION['player'] != ''){
	$player = $_SESSION['player'];
    } else {
	$player = get_first_player($game);
    }
	/* Variable for round number */
    $round = $_SESSION['round'];

?>
<!DOCTYPE html>
<html>
  <head>
    <title>I'm with stupid</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    
    <link href="css/bootstrap.css" rel="stylesheet" media="screen">
    
    <link rel="stylesheet" type="text/css" href="css/style.css">

  </head>
  <body>
  
  
    <script src="js/jquery-2.0.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <div class='container top-buffer'>
    
	<p id='navi-top'>Angemeldet als <?php echo $_SESSION['user'] ?> (<a href="logout.php" title="Abmelden">Abmelden</a>) | <a href="index.php" title="Spielauswahl">Zurück zur Spielauswahl</a></p>

	<form action="iws.php" name="load_round" id="load_round" class="form-horizontal" method="post">
		<input type="hidden" name="action" value="load_round">
		<div class="control-group">
		<h1><?php echo get_game_by_id($game); ?></h1>
		<div class="input-prepend ">
		 <span class="add-on">Runde:</span>
		 <select id="roundselect" name="round_to_load">
			<?php 
				//muss gebastelt werden
				$query = "SELECT DISTINCT round FROM iwsQuestion WHERE game=".intval($game)." ORDER BY round DESC";
				$result = mysql_query($query) or die("build round numbers dropdown: Anfrage fehlgeschlagen: " . mysql_error());
				$found = false;
				
				// HTML output
				while($row = mysql_fetch_array($result)){
					$number	= $row['round'];
					
					if($number == $round){
						$found = true;
						echo "\t\t\t<option selected='selected'>" . $number . "</option>\n";
					} else {
						echo "\t\t\t<option>" . $number . "</option>\n";
					}
				}
				if(!$found){
					echo "\t\t\t<option selected='selected'>" . $round . "</option>\n";
				}
				mysql_free_result($result);
			?>
		</select>
		</div>
		<button name="action" class="btn" value="start_new_round" type="submit"><?php echo "nächste Runde starten (" . (max(get_max_round($game), $round)+1) .")"; ?></button>
		</div>
	</form>
	
	<p style="clear:both"></p>
	
	<?php 
		if(isset($message) and $message != ''){
			echo "<div class='$message[0]'>
				<button type='button' class='close' data-dismiss='alert'>&times;</button>
				$message[1]
				</div>";
		}
	?>
  
<!-- Tabs -->
  <ul style="background-color: #eee;" class="nav nav-pills" id="navi">
    <li><a href="#player_management" data-toggle="tab">Spieler verwalten</a></li>
    <li><a href="#enter_answers" data-toggle="tab">Antworten eingeben</a></li>
    <li><a href="#questions_and_answers" data-toggle="tab">Fragen &amp; Antworten</a></li>
    <li><a href="#results" data-toggle="tab">Auswertung</a></li>
  </ul>
  
  <p style="clear:both"></p>
  
  <div class="tab-content">
<!-- Spielerverwaltung -->
  <div class="tabcontent tab-pane" id="player_management">
  <h2>Spielerverwaltung</h2>
	<div class='playermgmt'>
	<form action="iws.php" name="add_player" method="post" >
		<input type="hidden" name="active_tab" value="#player_management">
		<fieldset>
			<legend>Spieler anlegen</legend>
			<label>Neuer Spielername</label>
			<div class="input-append">
				<input type="text" name="player_name" placeholder="Name..." />
				<button type="submit" class="btn" name="action" value="add_player">Anlegen</button>
			</div>
		</fieldset>
	</form>
	</div>
	
	
	<div class='playermgmt'>
	<form action="iws.php" name="delete_player" method="post" >
		<input type="hidden" name="active_tab" value="#player_management">
		<fieldset>
			<legend>Spieler löschen</legend>
			<label>Spieler wählen</label>
			<div class="input-append">
				<?php 
					html_dropdown_list_of_players(get_first_player($game), $game);
				?>
				<button type="submit" class="btn" name="action" value="remove_player">Löschen</button>
			</div>
		</fieldset>
	</form>
	</div>
  </div>
  
  <p style="clear:both;"></p>
  

<!-- Antworteingabe -->
  <div class="tabcontent tab-pane active" id="enter_answers">
	<h2>Eingabe von Antworten | RUNDE <?php echo $round; ?></h2><br/>
	
	<form action="iws.php" name="load_answers_for_user_round" id="load_answers_for_user_round" method="post">
		<input type="hidden" name="action" value="load_answers_for_user_round">
		<div class="input-append">
			
			<?php 
				html_dropdown_list_of_players($player, $game, "player-round");
			?>
			
			<input type="hidden" name="active_tab" value="#enter_answers">
			<button name="action" class="btn" value="save_answers_for_user_round" type="submit">speichern</button>
		</div><br />
		
		<?php
			html_table_round_questions_answers_by_player($round, $player, $game);
		?>
		
	</form>

  </div>

  
<!-- Frage- und Antwortverwaltung -->
  <div class="tabcontent tab-pane" id="questions_and_answers">
	<h2>Fragen und Antworten in Runde <?php echo $round; ?></h2>
	
	<?php 
		html_list_questions_with_answers($round, $game);
	?>
	
	<form action="iws.php" name="add_question" method="post">
		<input type="hidden" name="active_tab" value="#questions_and_answers">
		<div class="input-prepend input-append">
                  <?php echo '<span class="add-on">Runde ' . $round . ', Frage ' . (get_no_of_questions($round, $game)+1) . '</span>' ?>:
                  <input name="questiontext" type="text" size="100" maxlength="1000" style="width:500px;">
                  <button name="action" value="add_question" class="btn" type="submit">hinzufügen</button>
            </div>
	</form>
  </div>
  
<!-- Punkteübersicht -->
  <div class="tabcontent tab-pane" id="results">
  
	<h2>Punktestand</h2>
	
	<ul class="nav nav-tabs" id="points">
	<li><a href="#res_current_round" data-toggle="tab">Ergebnisse</a></li>
	<li><a href="#points_for_answers_current_round" data-toggle="tab">Punkte für Antworten</a></li>
	<li><a href="#points_current_round" data-toggle="tab">Punktestand</a></li>
	<li><a href="#points_all" data-toggle="tab">Gesamtpunktestand</a></li>
	</ul>
	
	<div style="background:white;" class="tab-content">
		<div style="background:white;" class="tab-pane" id="res_current_round">
			<h4>Ergebnisse der aktuellen Runde</h4>
			<?php html_table_get_round($round, $game); ?>
		</div>
		
		<div style="background:white;" class="tab-pane" id="points_for_answers_current_round">
			<h4>Punkte für Antworten der aktuellen Runde</h4>
			<?php 
				echo '<div class="pull-left span5">';
				html_table_round_answers_points($round, $game);
				echo '</div><div class="pull-left">';
				html_bbcode_round_answers_points($round, $game);
				echo '</div>';
			?>
		</div>
		
		<div style="background:white;" class="tab-pane" id="points_current_round">
			<h4>Punktestand aktuelle Runde</h4>
			<?php 
				echo '<div class="pull-left span4">';
				html_table_round_player_points($round, $game);
				echo '</div><div class="pull-left">';
				html_bbcode_results_current_round($round, $game);
				echo '</div>';
			?>
		</div>
		
		<div style="background:white;" class="tab-pane" id="points_all">
			<h4>Punktestand insgesamt</h4>
			<?php 
				echo '<div class="pull-left span4">';
				html_table_sum_of_all_points($game);
				echo '</div><div class="pull-left">';
				html_bbcode_results($game);
				echo '</div>';
			?>
		</div>
	</div>
	
  <?php db_close($link); ?>
	
	
  </div>

  </div>
  
  </div>
  
  <script type=text/javascript>
	$('#navi a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	})
	$('#points a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	})
	
	$('#navi a[href="<?php echo $_SESSION['activetab']; ?>"]').tab('show');
	$('#points a[href="#points_current_round"]').tab('show');
	
	$('#roundselect').change(function() {
		$('#load_round').submit();
	});
	$('#player-round').change(function() {
		$('#load_answers_for_user_round').submit();
	});
	
	// mark
	$('#text_results_round').click(function() {
		var $this = $(this);
		$this.select();
	});
	$('#text_results_all').click(function() {
		var $this = $(this);
		$this.select();
	});
	$('#text_points_answers_round').click(function() {
		var $this = $(this);
		$this.select();
	});
  </script>
  </body>
</html>
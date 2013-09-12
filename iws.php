<?php
    session_start();
    
    include 'lib/db.php';
    include 'lib/html.php';
    include 'lib/action.php';
    
    $link = db_connect();

    if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] < 0){
        header("Location: login.php");
    }
    
    if(isset($_GET['id'])){
	$_SESSION['game_id'] = $_GET['id'];
	$_SESSION['round'] = get_max_round($_SESSION['game_id']);
	$_SESSION['player'] = get_first_player($_SESSION['game_id']);
    }
    
    if(!isset($_SESSION['game_id'])){
	header("Location: index.php");
    } else {
	$game = $_SESSION['game_id'];
    }
    
    if(isset($_SESSION['round'])){
	$round = $_SESSION['round'];
    } else {
	$round = 1;
    }
    
    if(isset($_POST['question_to_delete'])){
	isset($_POST["active_tab"]) ? $_SESSION['activetab'] = $_POST["active_tab"] : $activetab = "#add_question";
	delete_question(intval($_POST['question_to_delete']));
	$message[0] = 'alert';
	$message[1] = "Frage ".intval($_POST['question_to_delete'])." gelöscht.";
    } elseif(isset($_POST['answer_to_delete'])) {
	isset($_POST["active_tab"]) ? $_SESSION['activetab'] = $_POST["active_tab"] : $activetab = "#answers";
	delete_answer(intval($_POST['answer_to_delete']));
	$message[0] = 'alert';
	$message[1] = "Antwort ".intval($_POST['answer_to_delete'])." gelöscht.";
    } else {
	$message = post_action();
    }
    
    if(isset($_SESSION['round'])){
	$round = $_SESSION['round'];
    } else {
	$round = 1;
    }
    
    if(isset($_SESSION['player']) and $_SESSION['player'] != ''){
	$player = $_SESSION['player'];
    } else {
	$player = get_first_player($game);
    }
    
    if(get_user_for_game($game) != $_SESSION['user_id']){
	header("Location: index.php?err=1&user=".get_user_for_game($game)."&gameuser=".$_SESSION['user_id']);
    }


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
		<div class="control-group">
		<h1><?php echo get_game_by_id($game); ?></h1>
		<div class="input-prepend ">
		 <span class="add-on">Runde:</span>
		 <select id="roundselect" name="round">
			<?php 
				//muss gebastelt werden
				$query = "SELECT DISTINCT round FROM iwsQuestion WHERE game=".intval($game)." ORDER BY round DESC";
				$result = mysql_query($query) or die("build round numbers dropdown: Anfrage fehlgeschlagen: " . mysql_error());
				$found = false;
				
				// HTML output
				while($row = mysql_fetch_array($result)){
					$number	= $row['round'];
					print_r($row);
					
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
		<input type="hidden" name="player" value="<?php echo $player; ?>">
		<!--<button name="action" class="btn" value="load_round" type="submit">Runde laden</button>-->
		</div>
		<button name="action" class="btn" value="new_round" type="submit"><?php echo "nächste Runde starten (" . (max(get_max_round($game), $round)+1) .")"; ?></button>
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
    <li><a href="#enter_results" data-toggle="tab">Ergebnisse eingeben</a></li>
    <li><a href="#add_question" data-toggle="tab">Fragen &amp; Antworten</a></li>
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
		<input type="hidden" name="round" value="<?php echo $round; ?>">
		<input type="hidden" name="player" value="<?php echo $player; ?>">
		<fieldset>
		<legend>Spieler anlegen</legend>
		<label>Neuer Spielername</label>
		<div class="input-append">
			<input type="text" name="name" placeholder="Name..." />
			<button type="submit" class="btn" name="action" value="add_player">Anlegen</button>
		</div>
		</fieldset>
	</form>
	</div>
	
	
	<div class='playermgmt'>
	<form action="iws.php" name="delete_player" method="post" >

		<input type="hidden" name="active_tab" value="#player_management">
		<input type="hidden" name="round" value="<?php echo $round; ?>">
		<input type="hidden" name="player" value="<?php echo $player; ?>">
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
  

<!-- Ergebniseingabe -->
  <div class="tabcontent tab-pane active" id="enter_results">
	<h2>Eingabe von Ergebnissen | RUNDE <?php echo $round; ?></h2><br/>
	
	<form action="iws.php" name="load_user_round" id="load_user_round" method="post">
	<div class="input-append">
		
		<?php 
			html_dropdown_list_of_players($player, $game, "player-round");
		?>
		
		<input type="hidden" name="active_tab" value="#enter_results">
		<input type="hidden" name="round" value="<?php echo $round; ?>">
		<button name="action" class="btn" value="save_user_round" type="submit">speichern</button>
	</div><br />
	
		<?php
			html_table_round_questions_answers_by_player($round, $player, $game);
		?>
	
	</form>

  </div>

  
<!-- Frage hinzufügen -->
  <div class="tabcontent tab-pane" id="add_question">
	<h2>Fragen und Antworten in Runde <?php echo $round; ?></h2>
	
	<?php 
		//html_list_questions_of_round($round, $game); 
		html_list_questions_with_answers($round, $game);
	?>
	
	<form action="iws.php" name="add_question" method="post">
		<input type="hidden" name="active_tab" value="#add_question">
		<input type="hidden" name="round" value="<?php echo $round; ?>">
            <input type="hidden" name="player" value="<?php echo $player; ?>">
		<div class="input-prepend input-append">
                  <?php echo '<span class="add-on">Runde ' . $round . ', Frage ' . (get_no_of_questions($round, $game)+1) . '</span>' ?>:
                  <input name="questiontext" type="text" size="100" maxlength="1000" style="width:500px;">
                  <button name="action" value="add_question" class="btn" type="submit">hinzufügen</button>
            </div>
	</form>
  </div>

<!-- Liste der Antworten -->
  <!--<div class="tabcontent tab-pane" id="answers">
	<h2>Antworten in Runde <?php echo $round; ?></h2>
	
	<?php html_list_answers_of_round($round, $game); ?>
	
  </div>-->
  
<!-- Punkteübersicht -->
  <div class="tabcontent tab-pane" id="results">
  
	<h2>Punktestand</h2>
	
	<ul class="nav nav-tabs" id="points">
	<li><a href="#res_current_round" data-toggle="tab">Ergebnisse</a></li>
	<li><a href="#points_for_answers_current_round" data-toggle="tab">Punkte für Antworten</a></li>
	<li class="red"><a href="#points_current_round" data-toggle="tab">Punktestand</a></li>
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
		$('#load_user_round').submit();
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
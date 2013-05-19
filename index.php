<!DOCTYPE html>
<html>
  <head>
    <title>I'm with stupid</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="graphics/style.css">

  </head>
  <body>
  
    <div id="servermessages">
	<?php include 'lib/action.php' ?>
    </div>
    
      <p style="clear:both"></p>
  
    <?php $link = db_connect(); ?>
  
  <div id=round_management>
	<form action="index.php" name="load_round" method="post">
		Runde: <select name="round" size="1">
			<?php 
				//muss gebastelt werden
				$query = "SELECT DISTINCT number FROM iwsRound ORDER BY number DESC";
				$result = mysql_query($query) or die("build round numbers dropdown: Anfrage fehlgeschlagen: " . mysql_error());
				
				// HTML output
				while($row = mysql_fetch_array($result)){
					$number	= $row['number'];
					
					if($number == $round){
						echo "\t\t\t<option selected='selected'>" . $number . "</option>\n";
					} else {
						echo "\t\t\t<option>" . $number . "</option>\n";
					}
				}
				mysql_free_result($result);
			?>
		</select>
		<input type="hidden" name="player" value="<?php echo $player; ?>">
		<button name="action" value="load_round" type="submit">Runde laden</button>
		<button name="action" value="new_round" type="submit"><?php echo "nächste Runde starten (" . (get_max_round()+1) . ")" ?></button>
	</form>
	</div>
	
	<p style="clear:both"></p>
  
  <div id="tabrow">
	<button id="show_player_management">Spieler verwalten</button> 
	<button id="show_enter_results">Ergebnisse eingeben</button> 
	<button id="show_add_question">Frage hinzufügen</button> 
	<button id="show_results">Auswertung</button>
  </div>
  
  <p style="clear:both"></p>
  
  
  <div class="tabcontent" id="enter_results">
	<h1>Eingabe von Ergebnissen | RUNDE <?php echo $round; ?></h1><br/>
	
	<div>
	<form action="index.php" name="load_user_round" method="post">
	<select name="name" id="player-round" size="1">
			<?php 
				html_output_list_of_players($player);
			?>
			
		</select>
		
		<input type="hidden" name="round" value="<?php echo $round; ?>">
		<button name="action" value="load_user_round" type="submit">laden</button>
		<button name="action" value="save_user_round" type="submit">speichern</button>
	
	
		<?php 
			html_output_round_questions_answers_by_user($round, $player);
		?>
	
	</form>
	</div>

	
  </div>
  
  <div class="tabcontent" id="player_management">
  <h1>Spielerverwaltung</h1>
  
	<form action="index.php" name="add_player" method="post" style="float:left; border-right:5px groove #aaa;">
		<p>Name:<br><input name="name" type="text" size="12" maxlength="30"></p>
		<input type="hidden" name="action" value="add_player">
		<input type="hidden" name="round" value="<?php echo $round; ?>">
		<input type="hidden" name="player" value="<?php echo $player; ?>">
		<input type="submit" name="submit_add_player" value="Spieler anlegen">
	</form>
	
	
	<form action="index.php" name="delete_player" method="post" style="float:left;">
		<p>Name:<br><select name="name" size="1">
			<?php 
				html_output_list_of_players();
			?>
			
		</select></p>
		<input type="hidden" name="action" value="remove_player">
		<input type="hidden" name="round" value="<?php echo $round; ?>">
		<input type="hidden" name="player" value="<?php echo $player; ?>">
		<input type="submit" name="submit_delete_player" value="Spieler löschen">
		
	</form>
  </div>
  
  <div class="tabcontent" id="add_question">
	<h1>Frage zu aktueller Runde hinzufügen</h1>
	<form action="index.php" name="add_question" method="post">
		<?php echo "Runde " . $round . ", Frage " . (get_max_question_of_round($round)+1) ?>:
		<input name="questiontext" type="text" size="50" maxlength="100">
		<input type="hidden" name="round" value="<?php echo $round; ?>">
		<input type="hidden" name="player" value="<?php echo $player; ?>">
		<button name="action" value="add_question" type="submit">hinzufügen</button>
	</form>
  </div>
  
  <div class="tabcontent" id="results">
  
	<h1>Punktestand</h1>
	
	<div id="res_current_round">
		Ergebnisse der aktuellen Runde
		<?php html_output_get_round($round); ?>
	</div>
	
	<div id="points_for_answers_current_round">
		Punkte für Antworten der aktuellen Runde
		<?php html_output_round_answers_points($round); ?>
	</div>
	
	<div id="points_current_round">
		Punktestand aktuelle Runde
		<?php html_output_round_player_points($round); ?>
	</div>
	
	<div id="points_all">
		Punktestand insgesamt
		<?php html_output_sum_of_all_points(); ?>
	</div>
	
  <?php db_close($link); ?>
	
	
  </div>
  
  
  
  <script type=text/javascript>
	document.getElementById("enter_results").style.display = 'block';
	
	document.getElementById("show_enter_results").onclick = function () { 
		document.getElementById("enter_results").style.display = 'block';
		document.getElementById("player_management").style.display = 'none';
		document.getElementById("add_question").style.display = 'none';
		document.getElementById("results").style.display = 'none';
	}
	document.getElementById("show_player_management").onclick = function () { 
		document.getElementById("enter_results").style.display = 'none';
		document.getElementById("player_management").style.display = 'block';
		document.getElementById("add_question").style.display = 'none';
		document.getElementById("results").style.display = 'none';
	}
	document.getElementById("show_add_question").onclick = function () { 
		document.getElementById("enter_results").style.display = 'none';
		document.getElementById("player_management").style.display = 'none';
		document.getElementById("add_question").style.display = 'block';
		document.getElementById("results").style.display = 'none';
	}
	document.getElementById("show_results").onclick = function () { 
		document.getElementById("enter_results").style.display = 'none';
		document.getElementById("player_management").style.display = 'none';
		document.getElementById("add_question").style.display = 'none';
		document.getElementById("results").style.display = 'block';
	}
  </script>
  
  
	
	

  
  </body>
</html>
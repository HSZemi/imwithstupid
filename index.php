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
  
    <div id="servermessages">
	<?php include 'lib/action.php' ?>
    </div>

    
      <p style="clear:both"></p>
  
    <?php $link = db_connect(); ?>
  

	<form action="index.php" name="load_round" id="load_round" class="form-horizontal" method="post">
		<div class="control-group">
		<div class="input-prepend ">
		 <span class="add-on">Runde:</span>
		 <select id="roundselect" name="round">
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
		<!--<button name="action" class="btn" value="load_round" type="submit">Runde laden</button>-->
		</div>
		<button name="action" class="btn" value="new_round" type="submit"><?php echo "nächste Runde starten (" . (get_max_round()+1) . ")" ?></button>
		</div>
	</form>
	
	<p style="clear:both"></p>
  
<!-- Tabs -->
  <ul class="nav nav-pills" id="navi">
    <li><a href="#player_management" data-toggle="tab">Spieler verwalten</a></li>
    <li><a href="#enter_results" data-toggle="tab">Ergebnisse eingeben</a></li>
    <li><a href="#add_question" data-toggle="tab">Frage hinzufügen</a></li>
    <li><a href="#results" data-toggle="tab">Auswertung</a></li>
  </ul>
  
  <p style="clear:both"></p>
  
  <div class="tab-content">
<!-- Spielerverwaltung -->
  <div class="tabcontent tab-pane" id="player_management">
  <h1>Spielerverwaltung</h1>
  
	<form action="index.php" name="add_player" method="post" >
		
	
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
	
	
	<form action="index.php" name="delete_player" method="post" >

		<input type="hidden" name="active_tab" value="#player_management">
		<input type="hidden" name="round" value="<?php echo $round; ?>">
		<input type="hidden" name="player" value="<?php echo $player; ?>">
		<fieldset>
		<legend>Spieler löschen</legend>
		<label>Spieler wählen</label>
		<div class="input-append">
			<select name="name" size="1">
				<?php 
					html_output_list_of_players(get_first_player());
				?>
			</select>
			<button type="submit" class="btn" name="action" value="remove_player">Löschen</button>
		</div>
		</fieldset>
	</form>
  </div>
  

<!-- Ergebniseingabe -->
  <div class="tabcontent tab-pane active" id="enter_results">
	<h1>Eingabe von Ergebnissen | RUNDE <?php echo $round; ?></h1><br/>
	
	<form action="index.php" name="load_user_round" id="load_user_round" method="post">
	<div class="input-append">
		<select name="name" id="player-round" size="1">
			<?php 
				html_output_list_of_players($player);
			?>
			
		</select>
		
		<input type="hidden" name="active_tab" value="#enter_results">
		<input type="hidden" name="round" value="<?php echo $round; ?>">
		<!--<button name="action" class="btn" value="load_user_round" type="submit">laden</button>-->
		<button name="action" class="btn" value="save_user_round" type="submit">speichern</button>
	</div><br />
	
		<?php 
			html_output_round_questions_answers_by_user($round, $player);
		?>
	
	</form>

  </div>

  
<!-- Frage hinzufügen -->
  <div class="tabcontent tab-pane" id="add_question">
	<h1>Frage zu Runde <?php echo $round; ?> hinzufügen</h1>
	
	<?php html_list_questions_of_round($round); ?>
	
	<form action="index.php" name="add_question" method="post">
		<input type="hidden" name="active_tab" value="#add_question">
		<input type="hidden" name="round" value="<?php echo $round; ?>">
            <input type="hidden" name="player" value="<?php echo $player; ?>">
		<div class="input-prepend input-append">
                  <?php echo '<span class="add-on">Runde ' . $round . ', Frage ' . (get_max_question_of_round($round)+1) . '</span>' ?>:
                  <input name="questiontext" type="text" size="100" maxlength="1000" style="width:500px;">
                  <button name="action" value="add_question" class="btn" type="submit">hinzufügen</button>
            </div>
	</form>
  </div>
  
<!-- Punkteübersicht -->
  <div class="tabcontent tab-pane" id="results">
  
	<h1>Punktestand</h1>
	
	<ul class="nav nav-tabs" id="points">
	<li><a href="#res_current_round" data-toggle="tab">Ergebnisse</a></li>
	<li><a href="#points_for_answers_current_round" data-toggle="tab">Punkte für Antworten</a></li>
	<li class="red"><a href="#points_current_round" data-toggle="tab">Punktestand</a></li>
	<li><a href="#points_all" data-toggle="tab">Gesamtpunktestand</a></li>
	</ul>
	
	<div style="background:white;" class="tab-content">
		<div style="background:white;" class="tab-pane" id="res_current_round">
			Ergebnisse der aktuellen Runde
			<?php html_output_get_round($round); ?>
		</div>
		
		<div style="background:white;" class="tab-pane" id="points_for_answers_current_round">
			Punkte für Antworten der aktuellen Runde
			<?php html_output_round_answers_points($round); ?>
		</div>
		
		<div style="background:white;" class="tab-pane" id="points_current_round">
			Punktestand aktuelle Runde<br />
			<?php 
				echo '<div class="pull-left span4">';
				html_output_round_player_points($round);
				echo '</div><div class="pull-left">';
				html_bbcode_results_current_round($round);
				echo '</div>';
			?>
		</div>
		
		<div style="background:white;" class="tab-pane" id="points_all">
			Punktestand insgesamt<br />
			<?php 
				echo '<div class="pull-left span4">';
				html_output_sum_of_all_points();
				echo '</div><div class="pull-left">';
				html_bbcode_results();
				echo '</div>';
			?>
		</div>
	</div>
	
  <?php db_close($link); ?>
	
	
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
	
	$('#navi a[href="<?php echo $activetab; ?>"]').tab('show');
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
  </script>
  
  
	
	

  
  </body>
</html>
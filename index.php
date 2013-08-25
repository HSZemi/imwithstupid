<?php
    session_start();

    if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] < 0){
        header("Location: login.php");
    }
    include 'lib/db.php';
    include 'lib/html.php';
    
    if(isset($_POST['action']) and isset($_POST['name'])){
	$handle = db_connect();
	add_game($_POST['name']);
	db_close($handle);
    }

?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>iws - Spielübersicht</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta content="">
    <link href="css/bootstrap.css" rel="stylesheet" media="screen">
    
    <link rel="stylesheet" type="text/css" href="css/style.css">
  </head>
  <body>
  
  <div class="container top-buffer">
  <p id='navi-top'>Angemeldet als <?php echo $_SESSION['user'] ?> (<a href="logout.php" title="Abmelden">Abmelden</a>)</p>
  
    <div class="span6 offset3">
    
    <h1>Spielübersicht</h1>
	<?php 
	$handle = db_connect();
	html_list_of_games();
	db_close($handle);
	?>
	<hr />
	<form action="index.php" name="new_game" method="post" >
		<fieldset>
		<label>Neues Spiel:</label>
		<div class="input-append">
			<input type="text" name="name" placeholder="Name..." />
			<button type="submit" class="btn" name="action" value="new_game">Spiel anlegen</button>
		</div>
		</fieldset>
	</form>
    </div>
  </div>


  
  </body>
</html>
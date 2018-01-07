<?php
    session_start();

    if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] < 0){
        header("Location: login.php");
        die();
    }
    include 'lib/db.php';
    include 'lib/html.php';
    
    if(isset($_POST['action']) and isset($_POST['name'])){
	$handle = db_connect();
	if($_POST['name'] != ''){
		add_game($_POST['name'], $_SESSION['user_id']);
	}
	db_close($handle);
    }
    
    if(isset($_GET['err']) and intval($_GET['err']) == 1){
	$errormessage = '<div class="alert alert-error">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <strong>Das ist keins deiner Spiele.</strong> Lass den Unfug.
    </div>';
    } else {
	$errormessage = '';
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
  
  <script src="js/jquery-2.0.2.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <div class="container top-buffer">
  <p id='navi-top'>Angemeldet als <?php echo $_SESSION['user'] ?> (<a href="logout.php" title="Abmelden">Abmelden</a>)</p>
  
  <?php echo $errormessage; ?>
  
    <div class="span6 offset3">
    
    <h1>Spielübersicht</h1>
	<?php 
	$handle = db_connect();
	html_list_of_games($_SESSION['user_id']);
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
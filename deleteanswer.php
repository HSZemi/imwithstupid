<?php
    session_start();
    
    include 'lib/db.php';
    include 'lib/html.php';
    include 'lib/action.php';

    if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] < 0){
        header("Location: login.php");
        die();
    }
    
    if(!isset($_SESSION['game_id'])){
	header("Location: index.php");
	die();
    } else {
	$game = $_SESSION['game_id'];
    }
    
    $link = db_connect();
    
    if(get_user_for_game($game) != $_SESSION['user_id']){
	header("Location: index.php?err=1&user=".get_user_for_game($game)."&gameuser=".$_SESSION['user_id']);
	db_close($link);
	die();
    }
    
    if(!isset($_POST['answer_to_delete'])){
	header("Location: iws.php");
	db_close($link);
	die();
    } else {
	$answer_to_delete = $_POST['answer_to_delete'];
    }
    
    $_SESSION['activetab'] = '#questions_and_answers';
    
    

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
    
    <form action="iws.php" name="delete_answer" method="post">
    <p>Soll die Antwort mit der ID <?php echo $answer_to_delete . ' (<em>' . get_answer_by_id($answer_to_delete) . '</em>)'; ?> wirklich gelöscht werden?</p>
    <div class="form-actions text-center">
	<!--<button type="submit" class="btn" name="active_tab" value="#questions_and_answers">Abbrechen</button>-->
	<input type="hidden" name="action" value="delete_answer" />
	<a href="iws.php" class="btn">Abbrechen</a>
	<button type="submit" class="btn btn-danger" value="<?php echo $answer_to_delete; ?>" name="answer_to_delete">Löschen</button>
    </div>
	
    </form>
    
    </div>

  <?php db_close($link); ?>
  </body>
</html>
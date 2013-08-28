<?php
    session_start();
    
    include 'lib/db.php';
    include 'lib/html.php';
    include 'lib/action.php';
    
    $link = db_connect();

    if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] < 0){
        header("Location: login.php");
    }
    
    if(!isset($_SESSION['game_id'])){
	header("Location: index.php");
    } else {
	$game = $_SESSION['game_id'];
    }
    
    if(get_user_for_game($game) != $_SESSION['user_id']){
	header("Location: index.php?err=1&user=".get_user_for_game($game)."&gameuser=".$_SESSION['user_id']);
    }
    
    if(!isset($_POST['question_to_delete'])){
	header("Location: iws.php");
    } else {
	$question_to_delete = $_POST['question_to_delete'];
    }
    
    $_SESSION['activetab'] = '#add_question';
    
    

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
    
    <form action="iws.php" name="delete_question" method="post">
    <p>Soll die Frage mit der ID <?php echo $question_to_delete . ' (<em>' . get_question_by_id($question_to_delete) . '</em>)'; ?> wirklich gelöscht werden? <strong>Alle eingetragenen Antworten werden ebenfalls gelöscht!<strong></p>
    <div class="form-actions text-center">
	<button type="submit" class="btn" name="active_tab" value="#add_question">Abbrechen</button>
	<button type="submit" class="btn btn-danger" value="<?php echo $question_to_delete; ?>" name="question_to_delete">Löschen</button>
    </div>
	
    </form>
    
    </div>

  
  </body>
</html>
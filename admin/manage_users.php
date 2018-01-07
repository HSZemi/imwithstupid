<?php
    include '../lib/db.php';
    include '../lib/user.php';
    include '../lib/html.php';
    
    if(isset($_POST['action']) and $_POST['action'] === 'create_or_update_user'){
        if(isset($_POST['user']) and isset($_POST['pass'])){
            $user = $_POST['user'];
            $pass = $_POST['pass'];
            
            if($user != '' and $pass != ''){
			$link = db_connect();
			$updated = create_or_update_user($link, $user, $pass);
			db_close($link);
		} else {
			$updated = false;
		}
        }
    }
    
    if(isset($_POST['delete_user'])){
        $link = db_connect();
        $query = "DELETE FROM iwsUsers WHERE id = ".intval($_POST['delete_user']);
        $result = mysqli_query($link, $query);
        if(!$result){
            echo "delete_user_x: Anfrage fehlgeschlagen: " . mysql_error($link) . "<br/>";
            $deleted = false;
        } else {
		$deleted = true;
        }
        db_close($link);
    }
?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>iws - users</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta content="">
    <link href="../css/bootstrap.css" rel="stylesheet" media="screen">
    
    <link rel="stylesheet" type="text/css" href="../css/style.css">
  </head>
  <body>
  
  <div class="container top-buffer">
  
  <?php 

  if(!isset($updated) and !isset($deleted)){
	echo '<div class="span6 offset3 text-center"><span>&nbsp;</span><p>&nbsp;</p></div>'."\n";
  } else {
	if(isset($updated) and $updated){
		echo '<div class="span6 offset3"><span class="label label-success">Benutzer eingerichtet</span><p>&nbsp;</p></div>'."\n";
	} else if(isset($updated) and !$updated) {
		echo '<div class="span6 offset3"><span class="label label-important">Ein Fehler ist beim Updaten des Benutzers aufgetreten.</span><p>&nbsp;</p></div>'."\n";
	}
	if(isset($deleted) and $deleted){
		echo '<div class="span6 offset3"><span class="label label-success">Benutzer gelöscht</span><p>&nbsp;</p></div>'."\n";
	} else if(isset($deleted) and !$deleted) {
		echo '<div class="span6 offset3"><span class="label label-important">Ein Fehler ist beim Löschen des Benutzers aufgetreten.</span><p>&nbsp;</p></div>'."\n";
	}
  }
  
  ?>
    <div class="span6 offset3">
    
    
    <h1>Benutzerverwaltung</h1>
    <form class="form-horizontal" action="manage_users.php" method="post">
        <div class="control-group">
            <label class="control-label" for="inputUser">Benutzername</label>
            <div class="controls">
                <input type="text" id="inputUser" name="user" placeholder="Benutzername">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="inputPassword">Passwort</label>
            <div class="controls">
                <input type="password" id="inputPassword" name="pass" placeholder="Passwort">
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn btn-primary" name="action" value="create_or_update_user"><i class="icon-user icon-white"></i> Erstellen/Ändern</button>
            </div>
        </div>
        
        <?php $link = db_connect(); html_userlist($link); db_close($link); ?>
        
    </form>
    </div>
  </div>


  
  </body>
</html>
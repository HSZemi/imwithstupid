<?php
    session_start();

    if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] < 0){
        header("Location: login.php");
    }
    include 'lib/db.php';
    include 'lib/user.php';

    $md5_err = false;
    $updated = false;
    $deleted = false;
    $user_pass_problem = false;
    $user_privilegue_error = false;
    
    if(isset($_POST['user']) and isset($_POST['pass'])){
        $user = $_POST['user'];
        $pass = $_POST['pass'];
        
        if($user != "" and $pass != ""){
        
            $link = db_connect();

            if (CRYPT_MD5 == 1){
                if(isset($_POST['create_update'])){
                    if(get_id_of_user($link, $user) != $_SESSION['user_id']){
                        $user_privilegue_error = true;
                    } else {
                        $updated = create_or_update_user($link, $user, $pass);
                    }
                } elseif(isset($_POST['delete'])){
                    $deleted = delete_user($link, $user, $pass);
                }
            } else {
                $md5_err = true;
            }
            db_close($link);
        } else {
            $user_pass_problem = true;
        }
    }
?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>iws - Benutzerverwaltung</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta content="">
    <link href="css/bootstrap.css" rel="stylesheet" media="screen">
    
    <link rel="stylesheet" type="text/css" href="css/style.css">
  </head>
  <body>
  
  <div class="container top-buffer">
  
  <?php 
  
  if(!$md5_err and !$user_pass_problem and !$updated and !$deleted){
    echo '<div class="span6 offset3 text-center"><span>&nbsp;</span><p>&nbsp;</p></div>'."\n";
  }
  
  if($md5_err){
    echo '<div class="span6 offset3 text-center"><span class="label label-important">Fehler: MD5 nicht verfügbar!</span><p>&nbsp;</p></div>'."\n";
  }
  if($user_pass_problem){
    echo '<div class="span6 offset3 text-center"><span class="label label-warning">Fehler: Benutzername und/oder Passwort ungültig.</span><p>&nbsp;</p></div>'."\n";
  }
  if($user_privilegue_error){
    echo '<div class="span6 offset3 text-center"><span class="label label-important">Fehler: Nur Administratoren dürfen Benutzer anlegen und Passwörter anderer Benutzer ändern.</span><p>&nbsp;</p></div>'."\n";
  }
  if($updated){
    echo '<div class="span6 offset3 text-center"><span class="label label-success">Benutzer '.$user.' aktualisiert.</span><p>&nbsp;</p></div>'."\n";
  }
  if($deleted){
    echo '<div class="span6 offset3 text-center"><span class="label label-inverse">Benutzer '.$user.' wurde gelöscht.</span><p>&nbsp;</p></div>'."\n";
  }
  
  ?>
    <div class="span6 offset3">
    
    <h1>Benutzerverwaltung</h1>
    <form class="form-horizontal" action="user.php" method="post">
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
                <button type="submit" name="create_update" class="btn btn-primary"><i class="icon-user icon-white"></i> Erstellen/Updaten</button>
                <button type="submit" name="delete" class="btn btn-danger"><i class="icon-remove icon-white"></i> Löschen</button>
            </div>
        </div>
    </form>
    </div>
  </div>


  
  </body>
</html>
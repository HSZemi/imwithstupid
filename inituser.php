<?php
    session_start();

    if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] < 0){
        header("Location: login.php");
    }
    include 'lib/db.php';
    include 'lib/user.php';
	$db = db_connect();
    $updated = create_or_update_user('admin', 'admin');
    db_close($db);
     
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
  Benutzer admin:admin erstellt.
  </div>
  
  </body>
</html>

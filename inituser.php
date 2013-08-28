<?php
    session_start();


    include 'lib/db.php';
    include 'lib/user.php';
	$db = db_connect();
    $updated = create_or_update_user('admin', 'admin');
    $updated = create_or_update_user('keks', 'keks');
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
  Benutzer admin:admin erstellt.<br/>
  Benutzer keks:keks erstellt.<br/>
  
  <a href="login.php" title="Login">Zum Login</a>
  </div>
  
  </body>
</html>

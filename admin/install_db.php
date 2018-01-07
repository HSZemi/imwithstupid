<html>
<head><title>iws Installation</title></head>
<body>

<?php

//db-modul importieren
include '../lib/db.php';

// mit Datenbank verbinden
echo "Connect with database...";
$link = db_connect();
echo " successful.<br/>\n";

// ggf. alte Tabellen löschen
echo "Drop existing tables if exist...";
$query = "DROP TABLE IF EXISTS 
		iwsAnswers,
		iwsAnswer,
		iwsQuestion,
		iwsRound,
		iwsPlayers,
		iwsGames,
		iwsUsers;";
$result = mysqli_query($link, $query) or die("Anfrage fehlgeschlagen: " . mysql_error($link));
echo " done.<br/>\n";

// ggf. alte Views löschen
// noview
/*echo "Drop existing Views if exist...";
$query = "DROP VIEW IF EXISTS 
		bigtable,
		points_per_answer;";
$result = mysqli_query($link, $query) or die("Anfrage fehlgeschlagen: " . mysql_error($link));
echo " done.<br/>\n";*/



// Tabellen anlegen
echo "Creating tables<br/>\n";

echo "- CREATE TABLE iwsUsers<br />\n";
$query = "CREATE TABLE iwsUsers (
		id		int			AUTO_INCREMENT,
		username	VARCHAR(255)	UNIQUE,
		password	text,
		
		PRIMARY KEY (id)
	);";
$result = mysqli_query($link, $query) or die("Anfrage fehlgeschlagen: " . mysql_error($link));

echo "- Create table iwsGames<br/>\n";
$query = "CREATE TABLE iwsGames ( 
		id 		int AUTO_INCREMENT, 
		name 		varchar(255) UNIQUE NOT NULL, 
		user		int,
		
		PRIMARY KEY (id),
		FOREIGN KEY (user) REFERENCES iwsUsers(id) ON DELETE CASCADE ON UPDATE CASCADE
	);";
$result = mysqli_query($link, $query) or die("Anfrage fehlgeschlagen: " . mysql_error($link));

echo "- Create table iwsPlayer<br/>\n";
$query = "CREATE TABLE iwsPlayers ( 
		id 		int AUTO_INCREMENT, 
		name 		varchar(255) NOT NULL, 
		game		int,
		
		PRIMARY KEY (id),
		FOREIGN KEY (game) REFERENCES iwsGames(id) ON DELETE CASCADE ON UPDATE CASCADE,
		UNIQUE (name, game)
	);";
$result = mysqli_query($link, $query) or die("Anfrage fehlgeschlagen: " . mysql_error($link));

/*echo "- Create table iwsRound<br/>\n";
$query = "CREATE TABLE iwsRound ( 
		id		int,
		number 	int, 
		game		int,
		
		PRIMARY KEY (id),
		FOREIGN KEY (game) REFERENCES iwsGames(id) ON DELETE CASCADE ON UPDATE CASCADE
	);";
$result = mysqli_query($link, $query) or die("Anfrage fehlgeschlagen: " . mysql_error($link));*/

echo "- Create table iwsQuestion<br/>\n";
$query = "CREATE TABLE iwsQuestion ( 
		id 		int AUTO_INCREMENT, 
		round 	int, 
		value 	varchar(1024), 
		game		int,
		
		PRIMARY KEY (id),
		FOREIGN KEY (game) REFERENCES iwsGames(id) ON DELETE CASCADE ON UPDATE CASCADE
	);";
$result = mysqli_query($link, $query) or die("Anfrage fehlgeschlagen: " . mysql_error($link));

echo "- Create table iwsAnswer<br/>\n";
$query = "CREATE TABLE iwsAnswer (
		id		int AUTO_INCREMENT,
		question	int,
		value		varchar(255),
		game		int,
		
		PRIMARY KEY (id),
		FOREIGN KEY (question) REFERENCES iwsQuestion(id) ON DELETE CASCADE ON UPDATE CASCADE,
		FOREIGN KEY (game) REFERENCES iwsGames(id) ON DELETE CASCADE ON UPDATE CASCADE,
		UNIQUE (question, value)
	);";
$result = mysqli_query($link, $query) or die("Anfrage fehlgeschlagen: " . mysql_error($link));

echo "- Create table iwsAnswers<br/>\n";
$query = "CREATE TABLE iwsAnswers (
		id		int AUTO_INCREMENT,
		player	int,
		answer	int,
		game		int,
		
		PRIMARY KEY (id),
		FOREIGN KEY (player) REFERENCES iwsPlayers(id) ON DELETE CASCADE ON UPDATE CASCADE,
		FOREIGN KEY (answer) REFERENCES iwsAnswer(id) ON DELETE CASCADE ON UPDATE CASCADE,
		FOREIGN KEY (game) REFERENCES iwsGames(id) ON DELETE CASCADE ON UPDATE CASCADE,
		UNIQUE (player, answer)
	);";
$result = mysqli_query($link, $query) or die("Anfrage fehlgeschlagen: " . mysql_error($link));


echo "Creating tables done.<br/>\n";

// Datenbankverbindung trennen
echo "Closing connection<br/><br/>\n";
db_close($link);


echo "Installation successful.<br/>\n";
?>

</body>
</html>

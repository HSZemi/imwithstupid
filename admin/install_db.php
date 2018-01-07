<html>
<head><title>iws Installation</title></head>
<body>

<?php

//db-modul importieren
include '../lib/db.php';

// mit Datenbank verbinden
echo "Connect with database...";
$conn = db_connect();
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
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());
echo " done.<br/>\n";

// ggf. alte Views löschen
// noview
/*echo "Drop existing Views if exist...";
$query = "DROP VIEW IF EXISTS 
		bigtable,
		points_per_answer;";
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());
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
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());

echo "- Create table iwsGames<br/>\n";
$query = "CREATE TABLE iwsGames ( 
		id 		int AUTO_INCREMENT, 
		name 		varchar(255) UNIQUE NOT NULL, 
		user		int,
		
		PRIMARY KEY (id),
		FOREIGN KEY (user) REFERENCES iwsUsers(id) ON DELETE CASCADE ON UPDATE CASCADE
	);";
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());

echo "- Create table iwsPlayer<br/>\n";
$query = "CREATE TABLE iwsPlayers ( 
		id 		int AUTO_INCREMENT, 
		name 		varchar(255) NOT NULL, 
		game		int,
		
		PRIMARY KEY (id),
		FOREIGN KEY (game) REFERENCES iwsGames(id) ON DELETE CASCADE ON UPDATE CASCADE,
		UNIQUE (name, game)
	);";
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());

/*echo "- Create table iwsRound<br/>\n";
$query = "CREATE TABLE iwsRound ( 
		id		int,
		number 	int, 
		game		int,
		
		PRIMARY KEY (id),
		FOREIGN KEY (game) REFERENCES iwsGames(id) ON DELETE CASCADE ON UPDATE CASCADE
	);";
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());*/

echo "- Create table iwsQuestion<br/>\n";
$query = "CREATE TABLE iwsQuestion ( 
		id 		int AUTO_INCREMENT, 
		round 	int, 
		value 	varchar(1024), 
		game		int,
		
		PRIMARY KEY (id),
		FOREIGN KEY (game) REFERENCES iwsGames(id) ON DELETE CASCADE ON UPDATE CASCADE
	);";
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());

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
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());

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
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());


// Views anlegen
// noview
/*echo "- Create view points_per_answer<br/>\n";
$query = "CREATE VIEW points_per_answer AS (
		SELECT iwsAnswers.answer AS answer_id, COUNT(iwsAnswers.player) AS points
		FROM iwsAnswers
		GROUP BY iwsAnswers.answer
	);";
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());

echo "- Create view bigtable<br/>\n";
$query = "CREATE VIEW bigtable AS (
		SELECT iwsPlayers.id AS player_id, iwsPlayers.name AS player_name,
			iwsAnswers.id AS answers_id,
			iwsAnswer.id AS answer_id, iwsAnswer.value AS answer_value,
			iwsQuestion.id AS question_id, iwsQuestion.round AS round, iwsQuestion.number AS question_number, iwsQuestion.value AS question_value
		FROM (iwsAnswers JOIN iwsPlayers ON iwsAnswers.player = iwsPlayers.id) 
			JOIN (iwsAnswer JOIN iwsQuestion ON iwsAnswer.question = iwsQuestion.id) ON iwsAnswers.answer = iwsAnswer.id
	);";
$result = mysql_query($query) or die("Anfrage fehlgeschlagen: " . mysql_error());*/
echo "Creating tables done.<br/>\n";

// Datenbankverbindung trennen
echo "Closing connection<br/><br/>\n";
db_close($conn);


echo "Installation successful.<br/>\n";
?>

</body>
</html>

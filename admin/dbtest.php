<html>
<head><title>iws testsuite</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>
<body>

<?php

//db- und html-modul importieren
include '../lib/db.php';
include '../lib/html.php';

// mit Datenbank verbinden
$conn = db_connect();

$players = array('Lieschen Müller','hannskasper','t-bone','myrabilis','katreina','Erno der Erno','HSZemi');

foreach($players as $i){
	create_player($i);
}

create_round(2);

$questions_2 = array('Nenne eine beliebte Farbe für Ostereier',
'Nenne eine Tätigkeit im Schnee außer Ski alpin/Snowboarden',
'Nenne einen beliebten Weihnachtsmarkt',
'Nenne eine fiktionale Figur im Zusammenhang mit Weihnachten/Geschichte über Weihnachten',
'Nenne einen Film der im Dezember immer im Fernsehen läuft',
'Nenne ein typisches Silvestergericht',
'Nenne eine Nuss, die vor allem in der Vorweihnachtszeit gegessen wird',
'Nenne einen Gletscher in Österreich',
'Nenne eine beliebte Tätigkeit im Frühjahr',
'Nenne eine typische Veranstaltung im Mai');

$nr = 1;
foreach($questions_2 as $i){
	create_question(2, $nr, $i);
	$nr++;
}

$answers[0] = array('rot','grün','gelb','pink','bunt','blau');

$answers[1] = array('rodeln','Schneeballschlacht','Schneemann bauen','Langlauf');

$answers[2] = array('Nürnberger Christkindlmarkt','Dresdner Striezelmarkt','Stuttgarter Christkindlmarkt');

$answers[3] = array('Weihnachtsmann','Jesus','Ebenezer Scrooge','Gringe','Christkind','Der kleine Lord');

$answers[4] = array('Drei Nüsse für Aschenbrödel','Der kleine Lord','Dinner for one','Sissi','Kevin allein zuhaus','Santa Claus','Das Wunder von Manhattan');

$answers[5] = array('Fondue','Raclette','Karpfen','Nudelsalat','Sekt');

$answers[6] = array('Walnuss','Haselnuss','Mandel','Erdnuss 1');

$answers[7] = array('Stubai','Hintertux','Kitzsteinhorn','Dachstein','Kaunertal','Sölden');

$answers[8] = array('Frühjahrsputz','Gartenarbeit','Spazieren','Wandern','Radfahren','an der Bikinifigur arbeiten');

$answers[9] = array('Maibaum aufstellen','Tanz in den Mai','1. Mai in Kreuzberg','Vatertag','Walpurgisnacht','Muttertag','Maifeuer','TomFrasers Geburtstag');

for($i = 0; $i < 10; $i++){
	foreach($answers[$i] as $item){
//  		echo $questions_2[$i] . " --- " .  $item . "<br/>";
		create_answer($questions_2[$i], $item);
	}
}

create_round(1);
create_question(1,1,'Wie alt sind Sie?');
create_answer('Wie alt sind Sie?', 'unter 10');
create_answer('Wie alt sind Sie?', 'über 10');


add_answer_string('katreina', 'Nenne eine beliebte Farbe für Ostereier','grün');
add_answer_string('katreina', 'Nenne eine Tätigkeit im Schnee außer Ski alpin/Snowboarden','Schneeballschlacht');
add_answer_string('katreina', 'Nenne einen beliebten Weihnachtsmarkt','Nürnberger Christkindlmarkt');
add_answer_string('katreina', 'Nenne eine fiktionale Figur im Zusammenhang mit Weihnachten/Geschichte über Weihnachten','Jesus');
add_answer_string('katreina', 'Nenne einen Film der im Dezember immer im Fernsehen läuft','Drei Nüsse für Aschenbrödel');
add_answer_string('katreina', 'Nenne ein typisches Silvestergericht','Raclette');
add_answer_string('katreina', 'Nenne eine Nuss, die vor allem in der Vorweihnachtszeit gegessen wird','Haselnuss');
add_answer_string('katreina', 'Nenne einen Gletscher in Österreich','Kitzsteinhorn');
add_answer_string('katreina', 'Nenne eine beliebte Tätigkeit im Frühjahr','Frühjahrsputz');
add_answer_string('katreina', 'Nenne eine typische Veranstaltung im Mai', 'Walpurgisnacht');
add_answer_string('katreina', 'Wie alt sind Sie?', 'unter 10');

add_answer_number('HSZemi', 2, 1, 'grün');
add_answer_number('HSZemi', 2, 2, 'Schneemann bauen');
add_answer_number('HSZemi', 2, 3, 'Nürnberger Christkindlmarkt');
add_answer_number('HSZemi', 2, 4, 'Jesus');
add_answer_number('HSZemi', 2, 5,'Dinner for one');
add_answer_number('HSZemi', 2, 6, 'Karpfen');
add_answer_number('HSZemi', 2, 7, 'Haselnuss');
add_answer_number('HSZemi', 2, 8, 'Hintertux');
add_answer_number('HSZemi', 2, 9, 'Spazieren');
add_answer_number('HSZemi', 2, 10, '1. Mai in Kreuzberg');
add_answer_number('HSZemi', 1, 1, 'über 10');

add_answer_number('Erno der Erno', 2, 1, 'bunt');
add_answer_number('Erno der Erno', 2, 2, 'Schneemann bauen');
add_answer_number('Erno der Erno', 2, 3, 'Dresdner Striezelmarkt');
add_answer_number('Erno der Erno', 2, 4, 'Weihnachtsmann');
add_answer_number('Erno der Erno', 2, 5, 'Drei Nüsse für Aschenbrödel');
add_answer_number('Erno der Erno', 2, 6, 'Nudelsalat');
add_answer_number('Erno der Erno', 2, 7, 'Mandel');
add_answer_number('Erno der Erno', 2, 8, 'Dachstein');
add_answer_number('Erno der Erno', 2, 9, 'Spazieren');
add_answer_number('Erno der Erno', 2, 10, 'Tanz in den Mai');
add_answer_number('Erno der Erno', 2, 1, 'über 10');

html_output_round_player_points(2);
echo "<br/><br/>";
html_output_round_player_points(1);
echo "<br/><br/>";
html_output_sum_of_all_points();

html_output_get_round_points(2);

html_output_get_round(2);

html_output_get_all_rounds();





// Datenbankverbindung trennen
db_close($conn);


echo "tests successful."
?>

</body>
</html>

 
<html>
<head><title>iws testsuite</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>
<body>

<?php

//db-modul importieren
include '../lib/db.php';

// mit Datenbank verbinden
$conn = db_connect();

$players = ['Lieschen Müller','hannskasper','t-bone','myrabilis','katreina','Erno der Erno','HSZemi'];

foreach($players as $i){
	create_player($i);
}

create_round(2);

$questions_2 = ['Nenne eine beliebte Farbe für Ostereier',
'2. Nenne eine Tätigkeit im Schnee außer Ski alpin/Snowboarden',
'3. Nenne einen beliebten Weihnachtsmarkt',
'4. Nenne eine fiktionale Figur im Zusammenhang mit Weihnachten/Geschichte über Weihnachten',
'5. Nenne einen Film der im Dezember immer im Fernsehen läuft',
'6. Nenne ein typisches Silvestergericht',
'7. Nenne eine Nuss, die vor allem in der Vorweihnachtszeit gegessen wird',
'8. Nenne einen Gletscher in Österreich',
'9. Nenne eine beliebte Tätigkeit im Frühjahr',
'10. Nenne eine typische Veranstaltung im Mai'];

$nr = 1;
foreach($questions_2 as $i){
	create_question(2, $nr, $i);
	$nr++;
}

$answers[0] = ['rot','grün','gelb','pink','bunt','blau'];

$answers[1]= ['rodeln','Schneeballschlacht','Schneemann bauen','Langlauf'];

$answers[2] = ['Nürnberger Christkindlmarkt','Dresdner Striezelmarkt','Stuttgarter Christkindlmarkt'];

$answers[3] = ['Weihnachtsmann','Jesus','Ebenezer Scrooge','Gringe','Christkind','Der kleine Lord'];

$answers[4] = ['Drei Nüsse für Aschenbrödel','Der kleine Lord','Dinner for one','Sissi','Kevin allein zuhaus','Santa Claus','Das Wunder von Manhattan'];

$answers[5] = ['Fondue','Raclette','Karpfen','Nudelsalat','Sekt'];

$answers[6] = ['Walnuss','Haselnuss','Mandel','Erdnuss 1'];

$answers[7] = ['Stubai','Hintertux','Kitzsteinhorn','Dachstein','Kaunertal','Sölden'];

$answers[8] = ['Frühjahrsputz','Gartenarbeit','Spazieren','Wandern','Radfahren','an der Bikinifigur arbeiten'];

$answers[9] = ['Maibaum aufstellen','Tanz in den Mai','1. Mai in Kreuzberg','Vatertag','Walpurgisnacht','Muttertag','Maifeuer','TomFrasers Geburtstag'];

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
add_answer_string('katreina', '2. Nenne eine Tätigkeit im Schnee außer Ski alpin/Snowboarden','Schneeballschlacht');
add_answer_string('katreina', '3. Nenne einen beliebten Weihnachtsmarkt','Nürnberger Christkindlmarkt');
add_answer_string('katreina', '4. Nenne eine fiktionale Figur im Zusammenhang mit Weihnachten/Geschichte über Weihnachten','Jesus');
add_answer_string('katreina', '5. Nenne einen Film der im Dezember immer im Fernsehen läuft','Drei Nüsse für Aschenbrödel');
add_answer_string('katreina', '6. Nenne ein typisches Silvestergericht','Raclette');
add_answer_string('katreina', '7. Nenne eine Nuss, die vor allem in der Vorweihnachtszeit gegessen wird','Haselnuss');
add_answer_string('katreina', '8. Nenne einen Gletscher in Österreich','Kitzsteinhorn');
add_answer_string('katreina', '9. Nenne eine beliebte Tätigkeit im Frühjahr','Frühjahrsputz');
add_answer_string('katreina', '10. Nenne eine typische Veranstaltung im Mai', 'Walpurgisnacht');
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

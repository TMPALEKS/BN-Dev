<h1>Test Esportazione formato PDF e iCal</h1>

<?php
$events = array(
	array(
		"id" => 1,
		"start_date" => "2010-04-05 08:00:00",
		"end_date" => "2012-04-09 09:00:00",
		"text" => "text1",
		"rec_type" => "week_2___3,5",
		"event_pid" => null,
		"event_length" => 3600
	),

	array(
		"id" => 2,
		"start_date" => "2010-04-06 12:00:00",
		"end_date" => "2010-04-06 18:00:00",
		"text" => "text2",
		"rec_type" => "",
		"event_pid" => null,
		"event_length" => null
	),

	array(
		"id" => 3,
		"start_date" => "2010-04-07 12:00:00",
		"end_date" => "2010-04-07 18:00:00",
		"text" => "text3",
		"rec_type" => "",
		"event_pid" => null,
		"event_length" => null
	),

	array(
		"id" => 4,
		"start_date" => "2010-04-08 12:00:00",
		"end_date" => "2010-04-08 18:00:00",
		"text" => "text4",
		"rec_type" => "",
		"event_pid" => null,
		"event_length" => null
	)
);


require_once("lib/class.php");
$export = new ICalExporter();
$ical = $export->toICal($events);
file_put_contents("ical.ics", $ical);
?>
<a href="download_pdf.php">Scarica calendario PDF</a> <br />
<a href="ical.ics">Scarica calendario iCal</a>

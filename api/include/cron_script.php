<?php
require_once 'OauFaceWars.php';
$cron = new OauFaceWars();
$date_id = $cron->insertDate();
$competitors = $cron->generateCompetitors($date_id);
$cron->rankPreviousDate($date_id-1);

?>
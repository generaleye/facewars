#!/usr/bin/php
<?php
require_once 'OauFaceWars.php';
$cron = new OauFaceWars();
$test = $cron->cronTest();
$date_id = $cron->insertDate();
$competitors = $cron->generateCompetitors($date_id);
$cron->rankPreviousDate($date_id-1);

?>
#!/usr/bin/php
<?php
require_once 'OauFaceWars.php';
$cron = new OauFaceWars();
//$test = $cron->cronTest();
$date_id = $cron->insertDate();
if ($date_id != NULL) {
    $competitors = $cron->generateCompetitors($date_id);
    $cron->rankPreviousDate($date_id-1);
} else {
    echo "Date Exists";
}


?>
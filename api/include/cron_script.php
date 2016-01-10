#!/usr/bin/php
<?php
require_once 'OauFaceWars.php';
$cron = new OauFaceWars();
$today = date("Y-m-d", time());
if ($cron->doesDateExist($today)) {
    $current_status = $cron->getCurrentStatus($today);
    if ($current_status['current_status'] == 0) {
        $cron->rankPreviousDate($current_status['date_id']);
        $cron->updateCurrentStatus($today);
    }
}
$cron->loadCompetition();


//<!--#!/usr/bin/php-->
//require_once 'OauFaceWars.php';
//$cron = new OauFaceWars();
////$test = $cron->cronTest();
//$date_id = $cron->insertDate();
//if ($date_id != NULL) {
//    $competitors = $cron->generateCompetitors($date_id);
//    $cron->rankPreviousDate($date_id);
//} else {
//    echo "Date Exists";
//}
//
//
//?>
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

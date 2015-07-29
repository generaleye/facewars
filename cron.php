<?php

$dept = array("CSC","MEE","MSE","EEG","EGL");

$year = array(2010,2011,2012,2013,2014);

//url = http://eportal.oauife.edu.ng/pic.php?image_id=MSE/2010/03020142
//$url = $_GET['url'];


$imageArr = array();

while (count($imageArr)<5) {
    $matricNo = buildMatricNo($dept,$year);
    $url = "http://eportal.oauife.edu.ng/pic.php?image_id=".$matricNo."20142";
    if (checkUrl($url)) {
        array_push($imageArr,$matricNo);

        echo "<a href='".$url."'>".$url."</a><br />";

    }
}
var_dump($imageArr);

function checkUrl($url) {
    $mime_type = get_url_mime_type($url);
    if ($mime_type == 'image/jpeg') {
        return TRUE;
    }
    return FALSE;
}

function buildMatricNo($dept,$year) {
    $first = $dept[rand(0, count($dept)-1)];
    $second = $year[rand(0, count($year)-1)];
    $third = str_pad(rand(1,100),3,"0",STR_PAD_LEFT);
//    for ($i=001;$i<=100;$i++) {
//        echo str_pad($i,3,"0",STR_PAD_LEFT)."</br>";
//    }
    $matric = $first."/".$second."/".$third;
    //$url = "http://eportal.oauife.edu.ng/pic.php?image_id=".$first."/".$second."/".$third."20142";
    return $matric;
}

function get_url_mime_type($url) {

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_exec($ch);
    return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

}

?>
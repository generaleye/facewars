<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/15/15
 * Time: 11:41 PM
 */
echo date_default_timezone_get();
echo "</br>";
echo date("Y-m-d H:i:s", time());
echo "</br>";
date_default_timezone_set('Africa/Lagos');
echo "</br>";
echo date("Y-m-d H:i:s", time());
echo "</br>";
echo date_default_timezone_get();

//mkdir('../../images/'.date("Y-m-d", time()),0777);

$source = "http://eportal.oauife.edu.ng/pic.php?image_id=csc/2010/05120142";
$dest = $_ENV['OPENSHIFT_DATA_DIR']."images/".date("Y-m-d", time())."/uju.jpg";
copy($source, $dest);

echo $_ENV['OPENSHIFT_DATA_DIR'];
echo $dest;
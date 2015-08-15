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
date_default_timezone_set('America/Los_Angeles');
echo "</br>";
echo date("Y-m-d H:i:s", time());
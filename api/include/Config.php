<?php
/**
 * Database Configuration
 * Author: Generaleye
 */

if ($_SERVER["SERVER_NAME"]=="localhost") {
    define('DB_HOST', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', 'root');
    define('DB_NAME', 'oaufacewars');
    define('IMAGE_URL', 'http://localhost/oaufacewars/images/');
} elseif ($_SERVER["SERVER_NAME"]=="oau-facewars.rhcloud.com") {
    define('DB_HOST', '127.3.78.130');
    define('DB_USERNAME', 'admingRTgyYC');
    define('DB_PASSWORD', 'm_gi2P-rhRC1');
    define('DB_NAME', 'oau');
    define('IMAGE_URL', 'http:/oau-facewars.rhcloud.com/images/');
}


define('SENDGRID_USERNAME', 'generaleye');
define('SENDGRID_PASSWORD', 'sendgrid_password');
define('SENDGRID_FROM_EMAIL', 'developers@oaufacewars.com');
define('SENDGRID_FROM_NAME', 'OAUFACEWARS');

define('REGISTRATION_SUCCESSFUL', 0);
define('REGISTRATION_FAILED', 1);
define('EMAIL_ALREADY_EXISTS', 2);
define('USER_NOT_VERIFIED', 3);
define('LOGIN_SUCCESSFUL', 4);
define('UNSUCCESSFUL_LOGIN', 5);


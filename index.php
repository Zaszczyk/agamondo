<?php
ob_start();
session_start();
function myErrorHandler($errno, $errstr, $errfile, $errline){
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    echo $text = $errno.' '.$errstr.' '.$errfile.' '.$errline;

    //mail('ati_b@wp.pl', 'agamondo', $text, $headers);
}
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);
//set_error_handler(myErrorHandler, E_ALL ^ E_NOTICE);

require_once 'application/libs/Config.class.php';
require_once 'application/libs/Functions.class.php';

require_once 'application/libs/Router.class.php';
require_once 'application/controllers/Controller.php';
require_once 'application/models/Model.php';

// start the application
$app = new Router();
ob_end_flush();
?>

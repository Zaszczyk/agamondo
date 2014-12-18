<?php
ob_start();
session_start();
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);
require_once 'application/libs/Config.class.php';
require_once 'application/libs/Functions.class.php';

require_once 'application/libs/Router.class.php';
require_once 'application/controllers/Controller.php';
require_once 'application/models/Model.php';

// start the application
$app = new Router();
ob_end_flush();
?>

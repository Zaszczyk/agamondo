<?php

class AjaxController extends Controller{

    public $NoCSRFToken;

    public function __construct(){
        if($_SESSION['logged'] != 1)
            exit;

        $this->Path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
        $this->OpenDatabaseConnection();
    }


    public function __destruct(){

    }

}

?>
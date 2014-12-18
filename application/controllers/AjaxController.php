<?php

class AjaxController extends Controller{

    public $NoCSRFToken;

    public function __construct(){
        if($_SESSION['logged'] != 1)
            exit;

        $this->Path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
        $this->OpenDatabaseConnection();
    }

    public function getTrainingXml(){
        $TrainingModel = $this->LoadModel('TrainingModel');
        return $TrainingModel->getTrainingXml($_POST['id']);
    }

    public function __destruct(){
        //echo json_encode();
    }

}

?>
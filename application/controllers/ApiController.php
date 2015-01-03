<?php

class ApiController extends Controller{

    public $NoCSRFToken;

    public function __construct(){
        if($_POST['api_hash'] != Config::API_HASH)
            exit;

        $this->Path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
        $this->OpenDatabaseConnection();
    }

    public function test(){
        return 'sieeema';
    }

    public function addTraining(){
        $blob = fopen($this->Path.'trasa.tcx','rb');
        $distance = '7032.4';
        $time = '01:12:31';
        $calories = 1100;

        $TrainingModel = $this->loadModel('TrainingModel');
        try{
            $TrainingModel->addTraining($blob, $distance, $time, $calories);
        }
        catch(PDOException $e){
            echo $e;
        }

    }

    public function __destruct(){

    }

}

?>
<?php

class ApiController extends Controller{

    public $NoCSRFToken;

    public function __construct(){
        //if($_POST['api_hash'] != Config::API_HASH)
            //exit;

        $this->Path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
        $this->OpenDatabaseConnection();
    }

    public function test(){
        echo 'sieeema';
        if(isset($_POST['hash']))
            echo ', parametr: '.$_POST['hash'];
    }

    public function addTraining(){
        /*
        $xml = fopen($this->Path.'trasa.tcx','rb');
        $distance = '7032.4';
        $time = '01:12:31';
        $calories = 1100;
        */
        $distance = $_POST['distance'];
        $time = $_POST['time'];
        $calories = $_POST['calories'];
        $xml = $_POST['xml'];

        $TrainingModel = $this->loadModel('TrainingModel');
        try{
            $TrainingModel->addTraining($xml, $distance, $time, $calories);
            echo 'trening został zapisany';
        }
        catch(PDOException $e){
            echo $e;
        }

    }

    public function __destruct(){

    }

}

?>
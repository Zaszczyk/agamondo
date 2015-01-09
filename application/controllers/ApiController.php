<?php

class ApiController extends Controller{

    public $NoCSRFToken;
    public $Return = false;
    public function __construct(){
        if($_POST['api_hash'] != Config::API_HASH){
            $this->Return['type'] = 0;
            $this->Return['text'] = 'Nieprawidłowy parametr identyfikujący - hash.';
            exit;
        }


        $this->Path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
        $this->OpenDatabaseConnection();
    }

    public function login(){
        if(!empty($_POST['login']) && !empty($_POST['password'])){
            $loginLower = mb_strtolower($_POST['login']);

            $AuthModel = $this->loadModel('AuthModel');

            try{
                $result = $AuthModel->login($loginLower, $_POST['password']);
                if($result === false){
                    $this->Return['type'] = 0;
                    $this->Return['text'] = 'Logowanie nie powiodło się.';
                    return false;
                }

                $id = $AuthModel->getIdFromLogin($loginLower);

                if($id == null){
                    $this->Return['type'] = 0;
                    $this->Return['text'] = 'blad.';
                    return false;
                }
                $hash = $AuthModel->addLoggedUser($id);
                $this->Return['type'] = 0;
                $this->Return['text'] = 'Zostałeś pomyślnie zalogowany.';
                $this->Return['hash'] = $hash;
            }
            catch(PDOException $e){
                Functions::logger('PDO', $e);
            }


        }
        else{
            $this->Return['type'] = 0;
            $this->Return['text'] = 'Podaj login i hasło.';
        }
    }

    public function xml(){

        if(isset($_POST['xml']))
            echo 'parametr: '.$_POST['xml'];
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
        if($this->Return != false)
            echo json_encode($this->Return);
    }

}

?>
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

    public function index(){
        $this->Return['type'] = 0;
        $this->Return['text'] = 'Podaj nazwe funkcji po /.';
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

                $this->Return = $AuthModel->getDataFromLogin($loginLower);
                $id = $this->Return['id'];

                if($id == null){
                    $this->Return['type'] = 0;
                    $this->Return['text'] = 'blad.';
                    return false;
                }
                $hash = $AuthModel->addLoggedUser($id);
                $this->Return['type'] = 1;
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

    public function addTraining(){

        if(empty($_POST['hash']) || empty($_POST['xml'])){
            $this->Return['type'] = 0;
            $this->Return['text'] = 'Podaj parametry hash i xml.';
            return false;
        }

        $AuthModel = $this->loadModel('AuthModel');

        $user_id = $AuthModel->checkHashUserLogged($_POST['hash']);
        if($user_id == null){
            $this->Return['type'] = 0;
            $this->Return['text'] = 'Użytkownik z podanym hash nie jest zalogowany.';
            return false;
        }
        $xml = $_POST['xml'];

        /*$xml = str_replace('&lt;', '<', $xml);
        $xml = str_replace('&gt;', '>', $xml);*/

        $Training = new Training($xml);

        $TrainingModel = $this->loadModel('TrainingModel');
        try{

            $TrainingModel->addTraining($user_id, $xml, $Training->getActivity(), $Training->getDate(), $Training->getTime(), $Training->getDistance(),  $Training->getCalories(),  $Training->getTitle(), $Training->getDescription());
            $this->Return['type'] = 0;
            $this->Return['text'] = 'Trening został zapisany.';
        }
        catch(PDOException $e){
            echo $e;
        }
        catch(Exception $e){
            $this->Return['type'] = 0;
            $this->Return['text'] = $e->getMessage();
        }

    }

    public function __destruct(){
        if($this->Return != false)
            echo json_encode($this->Return);
    }

}

?>
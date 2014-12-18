<?php

class MainController extends Controller{

    public $NoCSRFToken;

    public function __construct($action = null){
        parent::__construct($action);

        if($_SESSION['logged'] != 1){
            $this->login();
            exit;
        }
    }

    public function index($page = null, $resp = null){
        $this->NoCSRFToken = NoCSRF::generate(Config::NOCSRF_SESSION_VARIABLE);


        require 'application/views/main/index.phtml';
    }

    public function deleteAnswer($page){
        if(ctype_digit($_POST['id'])){
            try{
                $AnswerModel = $this->loadModel('AnswerModel');
                $AnswerModel->deleteAnswer($_POST['id']);

                $resp['type'] = 1;
                $resp['text'] = 'Odpowiedź została usunięta.';
            }
            catch(PDOException $e){
                $resp = Functions::logger('PDO', $e);
            }
        }

        $this->index($page, $resp);
    }

    public function password($resp = null){
        $this->NoCSRFToken = NoCSRF::generate(Config::NOCSRF_SESSION_VARIABLE);
        require 'application/views/main/password.phtml';
    }

    public function changePassword(){
        $resp['type'] = 0;

        try{
            NoCSRF::check(Config::NOCSRF_SESSION_VARIABLE, $_POST, true, Config::NOCSRF_TOKEN_TIMEOUT);
        }
        catch(NoCSRFException $e){
            $resp['text'] = 'Nieprawidłowy token bezpieczeństwa, spróbuj ponownie.';
            $this->password($resp);
            return false;
        }

        if(mb_strlen($_POST['password1']) < 6){
            $resp['text'] = 'Hasło musi mieć co najmniej 6 znaków';
            $this->password($resp);
            return false;
        }

        $AuthModel = $this->loadModel('AuthModel');

        try{
            $result = $AuthModel->changePassword($_POST['old'], $_POST['password1'], $_POST['password2']);
        }
        catch(PDOException $e){
            Functions::logger('PDO', $e);
        }

        if($result === true){
            $resp['type'] = 1;
            $resp['text'] = 'Hasło zostało zmienione.';
        }
        elseif($result == -1){
            $resp['text'] = 'Hasła nie są takie same.';
        }
        elseif($result == -2){
            $resp['text'] = 'Stare hasło jest nieprawidłowe.';
        }

        $this->password($resp);
    }

    public function settings($resp = null){
        $this->NoCSRFToken = NoCSRF::generate(Config::NOCSRF_SESSION_VARIABLE);
        require 'application/views/main/settings.phtml';
    }

    public function addUser($resp = null, $inputs = null){
        $AuthModel = $this->loadModel('AuthModel');
        $Users = $AuthModel->getAllUsers();
        $this->NoCSRFToken = NoCSRF::generate(Config::NOCSRF_SESSION_VARIABLE);

        require 'application/views/main/adduser.phtml';
    }


    public function deleteUser(){
        try{
            NoCSRF::check(Config::NOCSRF_SESSION_VARIABLE, $_POST, true, Config::NOCSRF_TOKEN_TIMEOUT);
        }
        catch(NoCSRFException $e){
            $resp['type'] = 0;
            $resp['text'] = 'Nieprawidłowy token bezpieczeństwa, spróbuj ponownie.';
            $this->addUser($resp);
            return false;
        }

        if(ctype_digit($_POST['id'])){
            try{
                $AuthModel = $this->loadModel('AuthModel');
                $AuthModel->deleteUser($_POST['id']);

                $resp['type'] = 1;
                $resp['text'] = 'Użytkownik został usunięty';
            }
            catch(PDOException $e){
                $resp = Functions::logger('PDO', $e);
            }
        }

        $this->addUser($resp);
    }

    public function logout(){
        $AuthModel = $this->loadModel('AuthModel');
        $AuthModel->logout();

        header('Location: '.Config::PATH);
    }
}

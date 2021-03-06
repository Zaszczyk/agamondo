<?php

class AuthController extends Controller{

    public function index(){
        if($_SESSION['logged'] == true){
            header('Location: '.Config::PATH);
            exit;
        }

        require 'application/views/main/index.phtml';
    }

    public function login(){
        if($_SESSION['logged'] == true){
            header('Location: '.Config::PATH);
            return false;
        }

        if(isset($_POST['login']) && isset($_POST['password'])){

            $AuthModel = $this->loadModel('AuthModel');
            try{
                $result = $AuthModel->Login($_POST['login'], $_POST['password']);
            }
            catch(PDOException $e){
                Functions::logger('PDO', $e);
            }

            if($result === true){
                header('Location: '.Config::PATH);
                exit;
            }
            $resp['type'] = 0;
            $resp['text'] = 'Podałeś nieprawidłowe dane';
        }

        require 'application/views/auth/login.phtml';
    }

    public function forgetPassword(){
        require 'application/views/auth/forgetpassword.phtml';
    }

    public function sendRecoverPasswordEmail(){
        $email = trim($_POST['email']);
        $emailLower = mb_strtolower($email);

        $AuthModel = $this->LoadModel('AuthModel');

        try{
            $uid = $AuthModel->getIdFromEmail($emailLower);
        }
        catch(PDOException $e){
            $resp = Functions::logger('PDO', $e);
        }

        if(empty($uid)){
            $resp['type'] = 0;
            $resp['text'] = 'Brak konta o takim adresie';
        }
        else{
            $hash = Functions::GetRandomString(32);
            $hashSha1 = sha1($hash);

            try{
                $AuthModel->addNewRecoverPassword($uid, $hashSha1);

                $body = 'Aby zresetować hasło odwiedź ten link: <a href="'.Config::PATH.'auth/newpassword/'.$hash.'">'.Config::PATH.'auth/newpassword/'.$hash.'</a>';
                $this->_sendEmail($email, 'Odzyskiwanie hasła', $body);

                $resp['type'] = 1;
                $resp['text'] = 'E-mail z dalszymi instrukcjami został wysłany.';
            }
            catch(PDOException $e){
                Functions::logger('PDO', $e);
            }
        }

        require 'application/views/auth/forgetpassword.phtml';
    }

    public function newPassword($hash){
        $interval = '48 hour';

        if(mb_strlen($hash) != 32){
            $resp['type'] = 0;
            $resp['text'] = 'Musisz najpierw wykonać pierwszy krok procedury przywracania hasła.';
            require 'application/views/auth/forgetpassword.phtml';
            return false;
        }

        $AuthModel = $this->LoadModel('AuthModel');
        $hashSha1 = sha1($hash);
        try{
            $user = $AuthModel->getUserFromRecoverPassword($hashSha1, $interval);
        }
        catch(PDOException $e){
            Functions::logger('PDO', $e);
        }

        if(empty($user['id'])){
            $resp['type'] = 0;
            $resp['text'] = 'Musisz najpierw wykonać pierwszy krok procedury przywracania hasła.';
            require 'application/views/auth/forgetpassword.phtml';
            return false;
        }

        $password = Functions::GetRandomString(8);
        $options = array(
            'cost' => 9,
        );
        $hashAndSalt = password_hash($password, PASSWORD_BCRYPT, $options);

        try{
            $AuthModel->setNewPassword($user['id'], $hashAndSalt);
            $AuthModel->deleteRecoverPassword($hashSha1);
        }
        catch(PDOException $e){
            $resp = Functions::logger('PDO', $e);
        }

        $body = 'Twoje nowe hasło to: '.$password;
        $this->_sendEmail($user['email'], 'Odzyskiwanie hasła', $body);

        $resp['type'] = 1;
        $resp['text'] = 'Nowe hasło zostało wysłane na Twój adres email';

        require 'application/views/auth/login.phtml';
    }

    private function _registerValidation($post){
        $loginL = mb_strlen($post['login']);

        if($loginL < 1 || $loginL > 32)
            throw new Exception('Login może mieć 1-32 znaków.');

        if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL))
            throw new Exception('Podany e-mail jest nieprawidłowy.');

        $AuthModel = $this->LoadModel('AuthModel');

        if($AuthModel->checkEmail($post['email']) !== false)
            throw new Exception('Istnieje już użytkownik o podanym adresie e-mail.');

        if($AuthModel->checkLogin($post['login']) !== false)
            throw new Exception('Istnieje już użytkownik o podanym loginie.');

        if($post['password1'] != $post['password2'])
            throw new Exception('Hasła nie są takie same');

        return $AuthModel;
    }

    public function register(){
        if(!empty($_POST)){
            $value = $_POST;
            try{
                $AuthModel = $this->_registerValidation($_POST);
                $AuthModel->register($_POST['login'], $_POST['email'], $_POST['password1']);
                header('Location: '.Config::PATH.'auth/login/');
            }
            catch(PDOException $e){
                Functions::logger('PDO', $e);
            }
            catch(Exception $e){
                $resp['type'] = 0;
                $resp['text'] = $e->getMessage();
            }
        }

        require 'application/views/auth/register.phtml';
    }

    private function _sendEmail($email, $subject, $body){
        $headers = "MIME-Version: 1.0\n"; //naglowki odpowiadajce za wyswietlanie html
        $headers .= "Content-type: text/html; charset=utf-8\n";

        mail($email, $subject, $body, $headers);
    }
    public function editUser()
    {
        if($_SESSION['logged'] == false){
            header('Location: '.Config::PATH);
            return false;
        }

        try{
            $loginL = mb_strlen($_POST['login']);

            if($loginL < 1 || $loginL > 32)
                throw new Exception('Login może mieć 1-32 znaków.');

            if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
                throw new Exception('Podany e-mail jest nieprawidłowy.');

            $AuthModel = $this->LoadModel('AuthModel');

            if($AuthModel->checkEmail($_POST['email']) !== false)
                throw new Exception('Istnieje już użytkownik o podanym adresie e-mail.');

            if($AuthModel->checkLogin($_POST['login']) !== false)
                throw new Exception('Istnieje już użytkownik o podanym loginie.');

            $AuthModel->editUser($_SESSION['id'], $_POST["login"], $_POST["name"], $_POST["email"], $_POST['weight'],$_POST["height"]);

            $_SESSION['login'] = $_POST["login"];
            $resp['type'] = 1;
            $resp['text'] = 'Zmiany zostały zapisane';
        }
        catch(Exception $e){
            $resp['type'] = 0;
            $resp['text'] = $e->getMessage();
        }

        $this->edit();
    }
    public function edit()
    {
        if($_SESSION['logged'] == false){
            header('Location: '.Config::PATH);
            return false;
        }

        $AuthModel = $this->LoadModel('AuthModel');
        $user = $AuthModel->getUser($_SESSION['id']);
        require 'application/views/auth/edit.phtml';
    }

    public function password($resp = null){
        //$this->NoCSRFToken = NoCSRF::generate(Config::NOCSRF_SESSION_VARIABLE);
        require 'application/views/auth/password.phtml';
    }

    public function changePassword(){
        $resp['type'] = 0;

        try{
            //NoCSRF::check(Config::NOCSRF_SESSION_VARIABLE, $_POST, true, Config::NOCSRF_TOKEN_TIMEOUT);
        }
        catch(NoCSRFException $e){
            $resp['text'] = 'Nieprawidłowy token bezpieczeństwa, spróbuj ponownie.';
            $this->password($resp);
            return false;
        }

        if(mb_strlen($_POST['password1']) < 4){
            $resp['text'] = 'Hasło musi mieć co najmniej 4 znaki.';
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

}

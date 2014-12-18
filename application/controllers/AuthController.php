<?php

class AuthController extends Controller{

    public function index(){
        if($_SESSION['logged'] == true){
            header('Location: '.Config::PATH.'main/index/');
            exit;
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
                header('Location: '.Config::PATH.'auth/index/');
                exit;
            }
            $resp['type'] = 0;
            $resp['text'] = 'Podałeś nieprawidłowe dane';
        }

        require 'application/views/main/index.phtml';
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

    private function _sendEmail($email, $subject, $body){
        $headers = "MIME-Version: 1.0\n"; //naglowki odpowiadajce za wyswietlanie html
        $headers .= "Content-type: text/html; charset=utf-8\n";

        mail($email, $subject, $body, $headers);
    }

}

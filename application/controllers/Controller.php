<?php

class Controller{

    public $Db = null;
    public $Path = null;
    public $Lang = null;
    public $Action = null;

    public function __construct($action = null){
        $this->Action = strtolower($action);
        $this->Path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
        $this->OpenDatabaseConnection();
        if($_SESSION['logged'] == 1)
            require 'application/views/layouts/header_logged.phtml';
        else
            require 'application/views/layouts/header_unlogged.phtml';



    }

    /**
     * Open database connection
     * @return void
     */
    protected function OpenDatabaseConnection(){
        try{
            $this->Db = new PDO('mysql:host='.Config::DB_HOST.';dbname='.Config::DB_NAME, Config::DB_USER, Config::DB_PASSWORD);
            $this->Db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->Db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }
        catch(PDOException $e){
            Functions::Logger('PDOException', $e);
        }
    }


    /**
     * @param      $model
     * @param null $id
     * @return mixed
     */
    public function LoadModel($model){
        require_once 'application/models/'.$model.'.php';
        return new $model($this->Db);
    }


    public function login(){
        if(isset($_POST['login']) && isset($_POST['password'])){

            $AuthModel = $this->loadModel('AuthModel');

            try{
                $result = $AuthModel->Login($_POST['login'], $_POST['password']);
            }
            catch(PDOException $e){
                echo 'Błąd bazy danych.'.$e;
            }

            if($result === true){
                header('Location: '.Config::PATH.'auth/index/');
            }
        }

        $resp['text'] = 'Podałeś nieprawidłowe dane';
        require 'application/views/auth/login.phtml';
    }

    public function __destruct(){

        if($_SESSION['logged'] == 1)
            require __dir__.'/../views/layouts/footer_logged.phtml';
        else
            require __dir__.'/../views/layouts/footer_unlogged.phtml';
    }

}

<?php

class TrainingController extends Controller{

    public $NoCSRFToken;
    public $TrainingModel;

    public function __construct($action = null){
        parent::__construct($action);

        if($_SESSION['logged'] != 1){
            $this->login();
            exit;
        }

        $this->TrainingModel = $this->LoadModel('TrainingModel');
    }

    public function index(){
        $Results = $this->TrainingModel->getLastTraining();

        require 'application/views/main/index_logged.phtml';
    }

    public function display($id){
        if(!ctype_digit($id)){
            $this->error404();
            return false;
        }

        $Results = $this->TrainingModel->getTraining($id);
        require 'application/views/training/display.phtml';
    }

}

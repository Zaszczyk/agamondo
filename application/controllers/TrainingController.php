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
        $Results = $this->TrainingModel->getLastTrainings();

        require 'application/views/training/index.phtml';
    }

    public function display($id){
        if(!ctype_digit($id)){
            $this->error404();
            return false;
        }

        $Results = $this->TrainingModel->getTraining($id);

        require 'application/views/training/display.phtml';
    }

    public function add(){
        if(isset($_FILES['xml'])){

        }
        require 'application/views/training/add.phtml';
    }

}

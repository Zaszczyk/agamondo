<?php

class MainController extends Controller{

    public $NoCSRFToken;

    public function __construct($action = null){
        parent::__construct($action);
    }

    public function download(){
        $path = 'static/app/AgaMondo.apk';
        $size = filesize($path);

        header('Content-Type: application/x-download');
        header('Content-Disposition: attachment; filename=AgaMondo.apk');
        header('Content-Length: '.$size);
        header('Content-Transfer-Encoding: binary');

        readfile($path);
    }
    public function logout(){
        $AuthModel = $this->loadModel('AuthModel');
        $AuthModel->logout();

        header('Location: '.Config::PATH);
    }
}

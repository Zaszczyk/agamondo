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

    public function trainings($page){
        if(!ctype_digit($page)){
            $page = 1;
        }

        $Results = $this->TrainingModel->getTrainings($page, 3);
        $Pagination = $this->TrainingModel->getTrainingsPagination($page, 3);

        if(empty($Results) && $page != 1)
            $this->error404();

        else
            require 'application/views/training/display_all.phtml';
    }

    public function display_all(){
        $Results = $this->TrainingModel->getAllTrainings();

        require 'application/views/training/display_all.phtml';
    }

    public function display($id){
        if(!ctype_digit($id)){
            $this->error404();
            return false;
        }

        $Results = $this->TrainingModel->getTraining($id);

        require 'application/views/training/display.phtml';
    }

    private function _checkAddingErrors(){
        if ($_FILES['xml']['error'] > 0){
            switch($_FILES['xml']['error']){
                // jest większy niż domyślny maksymalny rozmiar,
                // podany w pliku konfiguracyjnym
                case 1: {
                    throw new Exception('Rozmiar pliku jest zbyt duży.');
                }

                // jest większy niż wartość pola formularza
                // MAX_FILE_SIZE
                case 2: {
                    throw new Exception('Rozmiar pliku jest zbyt duży.');
                }

                // plik nie został wysłany w całości
                case 3: {
                    throw new Exception('Plik wysłany tylko częściowo.');
                }

                // plik nie został wysłany
                case 4: {
                    throw new Exception('Nie wysłano żadnego pliku.');
                }

                // pozostałe błędy
                default: {
                    throw new Exception('Wystąpił błąd podczas wysyłania.');
                }
            }
        }
        elseif($_FILES['xml']['type'] != 'application/tcx+xml') {
            throw new Exception('Nieodpowiedni typ pliku.');
        }
    }

    public function add(){
        error_reporting(E_ALL ^ E_WARNING);
        ini_set('display_errors', 0);
        if(isset($_FILES['xml'])){

            try{
                $this->_checkAddingErrors();

                $xml = file_get_contents($_FILES['xml']['tmp_name']);
                if(mb_strlen($xml) < 50)
                    throw new Exception('Plik nie zawiera treningu.');

                $Training = new Training($xml);

                $TrainingModel = $this->loadModel('TrainingModel');
                $id = $TrainingModel->addTraining($_SESSION['id'], $xml, $Training->getDate(), $Training->getTime(), $Training->getDistance(), $Training->getCalories(), $_POST['title'], $_POST['description']);

                header('Location: '.Config::PATH.'training/display/'.$id);
            }
            catch(PDOException $e){
                echo $e;
            }
            catch(Exception $e){
                $resp['type'] = 0;
                $resp['text'] = $e->getMessage();
            }

        }
        require 'application/views/training/add.phtml';
    }
    public function summation($year){
        if(!ctype_digit($year)) {
            $year = 2015;
        }
        $year = $_POST['year'];
        for($i = 1; $i<=12; $i++)
            $Results[] = $this->TrainingModel->getTrainingMonth($i,$_POST['year']);

        require 'application/views/training/summation.phtml';
    }
    public function delete($id){
        if(!ctype_digit($id)){
            $this->error404();
            return false;
        }

        $Results = $this->TrainingModel->deleteTraining($id);

        $this->trainings(1);
    }

}

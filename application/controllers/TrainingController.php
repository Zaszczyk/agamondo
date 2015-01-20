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
            echo $_FILES['xml']['type'];
            if ($_FILES['xml']['error'] > 0)
            {
                echo 'problem: ';
                switch ($_FILES['xml']['error'])
                {
                    // jest większy niż domyślny maksymalny rozmiar,
                    // podany w pliku konfiguracyjnym
                    case 1: {echo 'Rozmiar pliku jest zbyt duży.'; break;}

                    // jest większy niż wartość pola formularza
                    // MAX_FILE_SIZE
                    case 2: {echo 'Rozmiar pliku jest zbyt duży.'; break;}

                    // plik nie został wysłany w całości
                    case 3: {echo 'Plik wysłany tylko częściowo.'; break;}

                    // plik nie został wysłany
                    case 4: {echo 'Nie wysłano żadnego pliku.'; break;}

                    // pozostałe błędy
                    default: {echo 'Wystąpił błąd podczas wysyłania.';
                    break;}
                }
            }
            elseif($_FILES['xml']['type'] != 'text/xml') {
                echo 'Nieodpowiedni typ pliku';
            }
            else{
                $xml = file_get_contents($_FILES['xml']['tmp_name']);
                $Training = new Training($xml);
                $title = 'bla';

                $TrainingModel = $this->loadModel('TrainingModel');
                try{
                    $TrainingModel->addTraining($_SESSION['id'], $xml, $Training->getDate(), $Training->getTime(), $Training->getDistance(), $Training->getCalories(), $title);
                    $this->Return['type'] = 0;
                    $this->Return['text'] = 'Trening został zapisany.';
                }
                catch(PDOException $e){
                    echo $e;
                }
            }
        }
        require 'application/views/training/add.phtml';
    }

}

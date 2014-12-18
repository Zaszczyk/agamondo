<?php

class Router{
    /** @var null The controller */
    private $_urlController = null;

    /** @var null The method (of the above controller), often also named "action" */
    private $_urlAction = null;

    /** @var null Parameter one */
    private $_urlPar1 = null;

    /** @var null Parameter two */
    private $_urlPar2 = null;

    /** @var null Parameter three */
    private $_urlPar3 = null;

    /**
     * "Start" the application:
     * Analyze the URL elements and calls the according controllers/method or the fallback
     */
    public function __construct(){

        // create array with URL parts in $url
        $this->SplitUrl();

        // check for controller: does such a controller exist ?
        if(file_exists('application/controllers/'.$this->_urlController.'.php')){
            // if so, then load this file and create this controller
            // example: if controller would be "car", then this line would translate into: $this->car = new car();
            require 'application/controllers/'.$this->_urlController.'.php';
            $ClassName = $this->_urlController;
            $this->_urlController = new $ClassName($this->_urlAction);

            // check for method: does such a method exist in the controller ?
            if(method_exists($this->_urlController, $this->_urlAction)){

                // call the method and pass the arguments to it
                if (isset($this->_urlPar3)){
                    $this->_urlController->{$this->_urlAction}($this->_urlPar1, $this->_urlPar2, $this->_urlPar3);
                }
                elseif(isset($this->_urlPar2)){
                    $this->_urlController->{$this->_urlAction}($this->_urlPar1, $this->_urlPar2);
                }
                elseif(isset($this->_urlPar1)){
                    $this->_urlController->{$this->_urlAction}($this->_urlPar1);
                }
                else{
                    $this->_urlController->{$this->_urlAction}();
                }
            }
            else{
                // default/fallback: call the index() method of a selected controller
                $this->_urlController->index();
            }

        }
        else{
            if($_SESSION['logged'] == 1){
                require 'application/controllers/MainController.php';
                $Main = new MainController();
                $Main->index();
            }
            else{
                require 'application/controllers/AuthController.php';
                $Auth = new AuthController();
                $Auth->index();
            }

        }
    }

    /**
     * Get and split the URL
     */
    private function SplitUrl(){

        if(isset($_GET['url'])){

            // split URL
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            
            // Put URL parts into according properties

            $this->_urlController = (isset($url[0]) ? ucWords($url[0]).'Controller' : null);
            $this->_urlAction = (isset($url[1]) ? ucWords($url[1]) : null);
            $this->_urlPar1 = (isset($url[2]) ? $url[2] : null);
            $this->_urlPar2 = (isset($url[3]) ? $url[3] : null);
            $this->_urlPar3 = (isset($url[4]) ? $url[4] : null);

        }
    }
}

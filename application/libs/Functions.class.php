<?php

/**
 * Class Functions
 *
 */
class Functions{

    /**
     * It generate random string
     * @param int $length
     * @return string
     */


    public static function getRandomString($length){
        $characters = 'vyzABCDVa012KLMNOubchijPQRSdefgTU789pqrstJWXkmno3456EFGwxHYZ';
        $randomString = '';
        $count = strlen($characters)-1;
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $count)];
        }
        return $randomString;
    }

    /**
     * Functions::Logger()
     * 
     * Funkcja do obsługi błędów i wyjątków
     * 
     * @param string $type
     * @param string $e
     * @return
     */
    public static function logger($type, $e){
        echo $e;
        $resp['type'] = 1;
        $resp['text'] = 'Wystąpił błąd';
        //mail(Config::ERROR_EMAIL, 'Wystąpił błąd na stronie '.Config::PATH, $e);
        return $resp;
    }

}

class NoCSRFException extends Exception{
    protected $code ;
    protected $message ;

    public function __construct($message, $code = null){
        $this->code = $code ;
        $this->message = $message ;
    }
}

function __autoload($classname) {
    if(file_exists($classname.".class.php"))
        require_once($classname.".class.php");
}
?>
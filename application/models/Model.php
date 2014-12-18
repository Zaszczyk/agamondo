<?php
/**
 * Auth Model
 * @package default
 */
abstract class Model{

	protected $_Db = null;
    public $Path = null;

    /**
     * Model::__construct()
     *
     * @param object $Db
     * @param integer $id
     * @return void
     */
    public function __construct($Db){
        $this->Path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
        $this->_Db = $Db;
    }
}
?>
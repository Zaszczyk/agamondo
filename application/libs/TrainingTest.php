<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 2015-01-20
 * Time: 21:35
 */

require '../libs/Functions.class.php';
require '../libs/Training.class.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);

class TrainingTest extends PHPUnit_Framework_TestCase {

    public function testGetTime(){

        $Training = new Training(file_get_contents('trainings-xml.xml'));
        $time = $Training->getDate();
        var_dump($time);
        $time = $Training->getTime();
        var_dump($time);
        $time = $Training->getDistance();
        var_dump($time);



        $time = $Training->getCalories();
        var_dump($time);
        $this->assertEquals('0:0:22', $time);

    }
}

<?php
/**
 * Auth Model
 * @package default
 */
class TrainingModel extends Model{

    public function __construct($Db){
        $this->_Db = $Db;
    }

    public function getAllTraining()
    {
        $sql = "SELECT id, distance, t_time, calories FROM training";
        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    public function addTraining($filePath, $distance, $t_time, $calories)
    {
        $blob = fopen($filePath,'rb');
        //$blob = file_get_contents($filePath);
        //$blob = mysql_real_escape_string($blob);
        $sql = "INSERT INTO training (xml, distance, t_time, calories) VALUES (:xml, :distance, :t_time, :calories)";
        $query = $this->db->prepare($sql);
        //$parameters = array(/*':xml' => $blob, ':title' => $title, ':type' => $type,*/ ':distance' => $distance, ':t_time' => $t_time,  ':calories' => $calories/*, ':description' => $description, ':created' => $created*/);

        $query->bindParam(':xml',$blob,PDO::PARAM_LOB);
        $query->bindParam(':distance',$distance);
        $query->bindParam(':t_time',$t_time);
        $query->bindParam(':calories',$calories);
        // useful for debugging: you can see the SQL behind above construction by using:
        //echo '[ PDO DEBUG ]: ' . Helper::debugPDO($sql, $parameters);  exit();

        $query->execute();
    }

    public function deleteTraining($training_id)
    {
        $sql = "DELETE FROM training WHERE id = :training_id";
        $query = $this->db->prepare($sql);
        $parameters = array(':training_id' => $training_id);

        // useful for debugging: you can see the SQL behind above construction by using:
        // echo '[ PDO DEBUG ]: ' . Helper::debugPDO($sql, $parameters);  exit();

        $query->execute($parameters);
    }

    public function getTraining($training_id)
    {
        $sql = "SELECT id, distance, t_time, calories FROM training WHERE id = :training_id LIMIT 1";
        $query = $this->db->prepare($sql);
        $parameters = array(':training_id' => $training_id);

        // useful for debugging: you can see the SQL behind above construction by using:
        // echo '[ PDO DEBUG ]: ' . Helper::debugPDO($sql, $parameters);  exit();

        $query->execute($parameters);

        // fetch() is the PDO method that get exactly one result
        return $query->fetch();
    }

    public function updateTraining($distance, $t_time, $calories, $training_id)
    {
        $sql = "UPDATE training SET distance = :distance, t_time = :t_time, calories = :calories WHERE id = :training_id";
        $query = $this->db->prepare($sql);
        $parameters = array(':distance' => $distance, ':t_time' => $t_time, ':calories' => $calories, ':training_id' => $training_id);

        // useful for debugging: you can see the SQL behind above construction by using:
        // echo '[ PDO DEBUG ]: ' . Helper::debugPDO($sql, $parameters);  exit();

        $query->execute($parameters);
    }

    public function getAmountOfTraining()
    {
        $sql = "SELECT COUNT(id) AS amount_of_training FROM training";
        $query = $this->db->prepare($sql);
        $query->execute();

        // fetch() is the PDO method that get exactly one result
        return $query->fetch()->amount_of_training;
    }
}
?>
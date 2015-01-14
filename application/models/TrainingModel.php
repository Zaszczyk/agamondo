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
        $sql = "SELECT id, distance, time, calories FROM trainings WHERE user_id= :uid";
        $query = $this->_Db->prepare($sql);
        $query->bindParam(':uid', $_SESSION['id']);
        $query->execute();

        return $query->fetchAll();
    }
    public function getLastTrainings()
    {
        $sql = "SELECT `trainings`.id,`trainings`.xml,`trainings`.title,`trainings`.distance, `trainings`.time FROM `trainings`
                WHERE user_id = :uid ORDER BY `trainings`.id DESC LIMIT 3 ";
        $query = $this->_Db->prepare($sql);
        $query->bindParam(':uid', $_SESSION['id']);
        $query->execute();

        return $query->fetchAll();
    }

    public function addTraining($user_id, $xml, $distance, $time, $calories)
    {

        $sql = "INSERT INTO trainings (user_id, xml, distance, time, calories) VALUES (:user_id, :xml, :distance, :time, :calories)";
        $query = $this->_Db->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        $query->bindParam(':xml',$xml,PDO::PARAM_LOB);
        $query->bindParam(':distance',$distance);
        $query->bindParam(':time',$time);
        $query->bindParam(':calories',$calories);
        $query->execute();
    }

    public function deleteTraining($training_id)
    {
        $sql = "DELETE FROM training WHERE id = :training_id WHERE user_id= :uid";
        $query = $this->_Db->prepare($sql);
        $parameters = array(':training_id' => $training_id);
        $query->bindParam(':uid', $_SESSION['id']);
        $query->execute($parameters);
    }

    public function getTraining($training_id)
    {
        $sql = "SELECT trainings.id, trainings.xml, trainings.title, trainings.activity, activities.name,
                trainings.distance, trainings.time, trainings.calories, trainings.description, trainings.created FROM trainings
                JOIN activities ON trainings.activity = activities.id
                WHERE trainings.id = :training_id LIMIT 1";
        $query = $this->_Db->prepare($sql);
        $parameters = array(':training_id' => $training_id);
        $query->execute($parameters);

        return $query->fetch();
    }

    public function updateTraining($distance, $t_time, $calories, $training_id)
    {
        $sql = "UPDATE training SET distance = :distance, t_time = :t_time, calories = :calories WHERE id = :training_id AND user_id= :uid";
        $query = $this->_Db->prepare($sql);
        $parameters = array(':distance' => $distance, ':t_time' => $t_time, ':calories' => $calories, ':training_id' => $training_id, ':uid' => $_SESSION['id']);
        $query->execute($parameters);
    }

    public function getAmountOfTraining()
    {
        $sql = "SELECT COUNT(id) AS amount_of_training FROM training AND user_id= :uid";
        $query = $this->_Db->prepare($sql);
        $query->bindParam(':uid', $_SESSION['id']);
        $query->execute();

        return $query->fetch()->amount_of_training;
    }

    public function getTrainingXml($id){
        $query = $this->_Db->prepare('SELECT xml FROM trainings WHERE id= :id AND user_id= :uid LIMIT 1');
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':uid',$_SESSION['id']);
        $query->execute();
        return $query->fetch();
    }
}
?>
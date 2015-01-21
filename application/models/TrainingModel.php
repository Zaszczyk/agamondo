<?php
/**
 * Auth Model
 * @package default
 */
class TrainingModel extends Model{

    public function __construct($Db){
        $this->_Db = $Db;
    }

    public function getTrainings($page, $perPage){

        $offset = $perPage * ($page - 1);
        $sql = "SELECT id, xml, title, distance, time FROM trainings WHERE user_id= :uid ORDER BY id DESC LIMIT :perpage OFFSET :offset";
        $query = $this->_Db->prepare($sql);
        $query->bindParam(':uid', $_SESSION['id'], PDO::PARAM_INT);
        $query->bindParam(':perpage', $perPage, PDO::PARAM_INT);
        $query->bindParam(':offset', $offset,PDO::PARAM_INT );
        $query->execute();

        return $query->fetchAll();
    }

    public function getTrainingsCount(){
        $sql = 'SELECT count(*) AS il FROM trainings WHERE user_id= :uid';
        $query = $this->_Db->prepare($sql);
        $query->bindParam(':uid', $_SESSION['id'], PDO::PARAM_INT);
        $query->execute();

        $ret = $query->fetch();
        return $ret['il'];
    }

    public function getTrainingsPagination($page, $perPage){

        $count = $this->getTrainingsCount();

        $string = '';
        $pages = ceil($count/$perPage);

        if($page-1 == 1 || $page > 2)
            $string = '<li class="pagination-first"><a href="training/trainings/1">&laquo;  &laquo; Pierwsza</a></li>';

        if($page > 2)
            $string .= '<li class="pagination-previous"><a href="training/trainings/'.($page-1).'">&laquo; Poprzednia</a></li>';

        $string .= '<li class="current">'.(($page-1)*$perPage+1).'-'.($page*$perPage > $count ? $count : $page*$perPage).'/'.$count.'</li>';

        if($page < $pages-1)
            $string .= '<li class="pagination-next"><a href="training/trainings/'.($page+1).'">Następna &raquo;</a></li>';

        if($pages > ($page+1) || $page+1 == $pages)
            $string .= '<li class="pagination-last"><a href="training/trainings/'.$pages.'">Ostatnia &raquo;  &raquo;</a></li>';

        return $string;
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

    public function addTraining($user_id, $xml, $date, $time, $distance, $calories, $title)
    {

        $sql = "INSERT INTO trainings (user_id, xml, distance, time, calories, date, title) VALUES (:user_id, :xml, :distance, :time, :calories, :date, :title)";
        $query = $this->_Db->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        $query->bindParam(':xml',$xml,PDO::PARAM_LOB);
        $query->bindParam(':distance',$distance);
        $query->bindParam(':time',$time);
        $query->bindParam(':calories',$calories);
        $query->bindParam(':date',$date);
        $query->bindParam(':title', $title);
        $query->execute();

        return $this->_Db->lastInsertId();
    }

    public function deleteTraining($training_id)
    {
        $sql = "DELETE FROM trainings WHERE id = :training_id WHERE user_id= :uid";
        $query = $this->_Db->prepare($sql);
        $parameters = array(':training_id' => $training_id);
        $query->bindParam(':uid', $_SESSION['id']);
        $query->execute($parameters);
    }

    public function getTraining($training_id)
    {
        $sql = "SELECT trainings.*, activities.name FROM trainings
                LEFT JOIN activities ON trainings.activity = activities.id
                WHERE trainings.id = :training_id LIMIT 1";
        $query = $this->_Db->prepare($sql);
        $parameters = array(':training_id' => $training_id);
        $query->execute($parameters);

        return $query->fetch();
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
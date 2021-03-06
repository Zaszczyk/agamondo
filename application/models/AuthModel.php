<?php
/**
 * Auth Model
 * @package default
 */
class AuthModel extends Model{

    public function __construct($Db){
        $this->_Db = $Db;
    }

    /**
     * AuthModel::Login()
     *
     * Logowanie
     *
     * @param string $login
     * @param string $password
     * @return boolean
     */
	public function login($login, $password){

		$login = htmlspecialchars(trim($login));

        $query = $this->_Db->prepare('SELECT password FROM users WHERE login= :login LIMIT 1');
        $query->bindParam(':login', $login, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetch(PDO::FETCH_ASSOC);

	    if($results['password'] == null){
            $this->_AddUserLogging($login, false);
			return false;  
	    }
	                
	    $auth = password_verify($password, $results['password']);

        if($auth){
            $this->_AddUserLogging($login, true);

            $query = $this->_Db->prepare('SELECT id, login, name FROM users WHERE LOWER(login)= :login LIMIT 1');
            $query->bindParam(':login', $login, PDO::PARAM_STR);
            $query->execute();
            $results = $query->fetch(PDO::FETCH_ASSOC);

            $_SESSION['id'] = $results['id'];
            $_SESSION['login'] = $results['login'];
            $_SESSION['logged'] = true;
            session_regenerate_id(true);

            return true;
        }
        else{
            $this->_addUserLogging($login, false);
            return false;
        }

	}

    public function ChangePassword($old, $pass1, $pass2){
        $options = array('cost' => 9);

        if($pass1 != $pass2){
            return -1;           
        }
        $query = $this->_Db->prepare('SELECT password FROM users WHERE id= :id LIMIT 1');
        $query->bindParam(':id', $_SESSION['id'], PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetch(PDO::FETCH_ASSOC);

        $auth = password_verify($old, $results['password']);

        if($results['password'] == null || $auth == false){
            return -2;
        }
        elseif($auth){
            $new = password_hash($pass1, PASSWORD_BCRYPT, $options);

            $query = $this->_Db->prepare('UPDATE users SET password= :new WHERE id= :id');
            $query->bindParam(':new', $new, PDO::PARAM_STR);
            $query->bindParam(':id', $_SESSION['id'], PDO::PARAM_INT);
            return $query->execute();
        }

    }
    public function editUser($id, $login, $name, $email, $weight, $height)
    {
        $sql = "UPDATE users SET login = :login, name = :name, email = :email, weight = :weight, height = :height
                WHERE id = :user_id";
        $query = $this->_Db->prepare($sql);
        $parameters = array(':login' => $login, ':name' => $name, ':email' => $email,
            ':weight' => $weight, ':height' => $height, ':user_id' => $id);
        $query->execute($parameters);
    }


    public function checkLogin($login){
        $login = mb_strtolower($login);

        if($_SESSION['logged'] == true){
            $query = $this->_Db->prepare('SELECT id FROM users WHERE LOWER(login)= :login AND id != :id LIMIT 1');
            $query->bindParam(':id', $_SESSION['id'], PDO::PARAM_INT);
        }
        else
            $query = $this->_Db->prepare('SELECT id FROM users WHERE LOWER(login)= :login LIMIT 1');

        $query->bindParam(':login', $login, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if(isset($result['id']))
            return true;
        else
            return false;
    }

    public function checkEmail($email){
        $email = mb_strtolower($email);

        if($_SESSION['logged'] == true){
            $query = $this->_Db->prepare('SELECT id FROM users WHERE LOWER(email)= :email AND id != :id LIMIT 1');
            $query->bindParam(':id', $_SESSION['id'], PDO::PARAM_INT);
        }
        else
            $query = $this->_Db->prepare('SELECT id FROM users WHERE LOWER(email)= :email LIMIT 1');

        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if(isset($result['id']))
            return $result['id'];
        else
            return false;
    }

    public function register($login, $email, $password){
        $options = array(
            'cost' => 9,
        );

        $password = password_hash($password, PASSWORD_BCRYPT, $options);

        $query = $this->_Db->prepare('INSERT INTO users(login, email, password, date_register) VALUES(:login, :email, :password, NOW())');
        $query->bindParam(':login', $login, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->execute();
    }


    public function getIdFromEmail($emailLower){
        $query = $this->_Db->prepare('SELECT id FROM users WHERE LOWER(email)= :email LIMIT 1');
        $query->bindParam(':email', $emailLower, PDO::PARAM_STR);
        $query->execute();
        $ret =  $query->fetch();
        return $ret['id'];
    }

    public function getDataFromLogin($login){
        $query = $this->_Db->prepare('SELECT id, login, name, weight, height, level_id  FROM users WHERE LOWER(login)= :login LIMIT 1');
        $query->bindParam(':login', $login, PDO::PARAM_STR);
        $query->execute();
        return $query->fetch();
    }

    public function addNewRecoverPassword($uid, $hash){
        $query = $this->_Db->prepare('INSERT INTO recover_password(uid, hash, date) VALUES(:uid, :hash, NOW()) ON DUPLICATE KEY UPDATE date = NOW(), hash = :hash');
        $query->bindParam(':uid', $uid, PDO::PARAM_INT);
        $query->bindParam(':hash', $hash, PDO::PARAM_STR, 40);
        $query->execute();
    }

    public function getUserFromRecoverPassword($hash, $interval){
        $query = $this->_Db->prepare('SELECT a.uid AS id, (SELECT email FROM users AS b WHERE b.id = a.uid LIMIT 1) AS email FROM recover_password AS a WHERE hash= :hash LIMIT 1');
        $query->bindParam(':hash', $hash, PDO::PARAM_STR);
        $query->execute();
        return $query->fetch();
    }

    public function setNewPassword($id, $hashAndSalt){
        echo $id.$hashAndSalt;
        $query = $this->_Db->prepare('UPDATE users SET password= :new WHERE id= :id');
        $query->bindParam(':new', $hashAndSalt, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
    }

    public function deleteRecoverPassword($hash){
        $query = $this->_Db->prepare('DELETE FROM recover_password WHERE hash= :hash LIMIT 1');
        $query->bindParam(':hash', $hash, PDO::PARAM_STR);
        $query->execute();
    }

    public function addLoggedUser($uid){
        $hash = Functions::getRandomString(32);
        $hashSha224 = hash('SHA224', $hash);

        $query = $this->_Db->prepare('INSERT INTO users_logged(user_id, hash, first_login) VALUES(:uid, :hash, NOW())');
        $query->bindParam(':uid', $uid, PDO::PARAM_INT);
        $query->bindParam(':hash', $hashSha224, PDO::PARAM_STR);
        $query->execute();

        return $hash;
    }

    public function logout(){
        $_SESSION['logged'] = false;
        session_unset();
        session_destroy();
        session_regenerate_id(true);
        $_SESSION = array();
    }


    private function _addUserLogging($login, $success){

        $query = $this->_Db->prepare('INSERT INTO users_logins(login, success, ip) VALUES(:login, :success, INET_ATON(:ip))');
        $query->bindParam(':login', $login, PDO::PARAM_STR);
        $query->bindParam(':success', $success, PDO::PARAM_BOOL);
        $query->bindParam(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $query->execute();
    }
    public function getUser($user_id){
        $sql = "SELECT login, name, email, weight, height FROM users
                WHERE id = :user_id LIMIT 1";
        $query = $this->_Db->prepare($sql);
        $parameters = array(':user_id' => $user_id);
        $query->execute($parameters);

        return $query->fetch();
    }

    public function checkHashUserLogged($hash){
        $hash = hash('SHA224', $hash);
        $query = $this->_Db->prepare('SELECT user_id FROM users_logged WHERE hash= :hash LIMIT 1');
        $query->bindParam(':hash', $hash, PDO::PARAM_STR);
        $query->execute();
        $ret =  $query->fetch();
        return $ret['user_id'];
    }
}
?>
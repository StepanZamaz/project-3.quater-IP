<?php


abstract class BaseDBPage extends BasePage
{

    protected ?PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo= DB::getConnection();
        $this->checkAdmin();
        $this->checkLogin();
    }
    protected function checkLogin() : void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $log = false;


        if(!$_SESSION['loggedIn']){
            if(isset($_POST['password']) && isset($_POST['aName'])){
                $password = $_POST['password'];
                $name = $_POST['aName'];
                $stmt = $this->pdo->query("SELECT name, password FROM employee");
                $stmt->execute();

                while($row = $stmt->fetch($this->pdo::FETCH_ASSOC)){

                    if($name === $row['name']){

                        if(password_verify("$password", $row["password"])){
                            $log = true;
                        }
                    }
                }
            }
            if($log){
                $_SESSION['loggedIn'] = true;
            }
            else $_SESSION['loggedIn'] = false;
        }
    }
    protected function checkAdmin() : void
    {
        $log = false;
        //$_SESSION['loggedIn'] = false;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        //dump($_SESSION);


            if(isset($_POST['aName'])){
                //dumpe($_POST);
                $name = $_POST['aName'];
                $stmt = $this->pdo->query("SELECT name, admin FROM employee");
                $stmt->execute();

                while($row = $stmt->fetch($this->pdo::FETCH_ASSOC)){

                    if($name === $row['name']){
                        if($row['admin'] === 1){
                            $log =  true;;
                        }
                    }
                }
                if($log){
                    $_SESSION['isAdmin'] = true;
                }
                else {
                    $_SESSION['isAdmin'] = false;
                    //var_dump($_SESSION);
                }
            }


    }

}
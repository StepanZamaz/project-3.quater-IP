<?php
require "../includes/bootstrap.inc.php";



final class ListRoomsPage extends BaseDBPage{


    protected function setUp(): void
    {
        parent::setUp();
        $this->title="Seznam místností";
    }

    protected function body(): string
    {
        if(isset($_POST['logout'])){
            $logout = $_POST['logout'];
            if($logout === "logout"){
                $_SESSION['loggedIn'] = false;
            }
        }
        if($_SESSION['loggedIn']) {
            $stmt = $this->pdo ->query("SELECT * FROM room");
            $stmt->execute([]);
            return $this->m->render("roomList",["roomDetail"=>"room.php", "rooms" =>$stmt,"admin"=>$_SESSION['isAdmin']]);
        }
        else if (!$_SESSION['loggedIn']) {

            return $this->m->render("login");
        }
    }
}

$page = new ListRoomsPage();
$page->render();
<?php
require "../includes/bootstrap.inc.php";

final class ListEmployeePage extends BaseDBPage
{


    protected function setUp(): void
    {
        parent::setUp();
        $this->title = "Seznam zaměstnanců";
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
            $stmt = $this->pdo->query('SELECT employee.name as "namePeople",surname,job,phone,employee_id,room.name as "nameRoom" FROM employee JOIN room ON employee.room = room.room_id');
            $stmt->execute([]);
            return $this->m->render("employeeList", ["employeeDetail" => "employee.php", "employees" => $stmt ,"isAdmin"=>$_SESSION['isAdmin']]);
        }
        else if (!$_SESSION['loggedIn']) {
            return $this->m->render("login");
        }
    }
}

$page = new ListEmployeePage();
$page->render();
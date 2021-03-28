<?php
require "../includes/bootstrap.inc.php";



final class CreateEmployeePage extends BaseCRUDPage{
    private EmployeeModel $employee; //!!
    private array $rooms;

    protected function setUp(): void
    {
        if(!$_SESSION['isAdmin']){
            header("Location: ../employee/employeeList.php");
            exit;
        }
        if(!$_SESSION['loggedIn']){
            header("Location: ../employee/employeeList.php");
            exit;
        }
        parent::setUp();

        $this->state= $this->getState();

        if($this->state === self::STATE_PROCESSED){
            //HOTOVK
            if($this->result === self::RESULT_SUCCESS){
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='5;url=./employeeList.php'>";
                $this->title = "Zaměstnanec přidán";
            }elseif($this->result === self::RESULT_FAIL){
                $this->title = "Vytvoření zaměstnance selhalo";
            }
        }
        else if($this->state === self::STATE_FORM_SENT){
            //NAČÍST DATA,

            $this->employee = $this->readPost();

            // VALIDOVAT DATA,
            if($this->employee->isValid()){

                //ULOZIT A PRESMEROVAT
                $token = bin2hex(random_bytes(20));

                if($this->employee->insert()){
                    $this->createKey($this->employee->getPrimaryKey());
                    //uspech
                    $this->sessionStorage->set($token, ['result' => self::RESULT_SUCCESS]);
                } else {
                    //neuspech

                    $this->sessionStorage->set($token, ['result' => self::RESULT_FAIL]);
                }
                $this->redirect($token);


            }else{// JIT NA FORMULAR NEBO
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title="Přidat zaměstnance: Neplatný formulář";
            }
        }
        else{
            //PREJIT NA FORMULAR
            $this->title="Vytvořit zaměstnance";
            $this->rooms = $this->selectRoom();
            $this->employee = new EmployeeModel(); //!!
        }

    }

    protected function body(): string
    {
        if($this->state === self::STATE_FORM_REQUESTED){
            return $this->m->render("employeeForm", ['create' => true ,'employee' => $this->employee,'rooms'=>$this->rooms]); //!!
        }elseif ($this->state === self::STATE_PROCESSED){
            if($this->result === self::RESULT_SUCCESS){
                return $this->m->render("employeeSuccess",["message" => "Zaměstnanec byl úspěšně přidán."]);
            }elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("employeeFail",["message"=>"Přidání zaměstnance selhalo."]);
            }
        }
    }

    protected function getState() : int{
        //PROCESSED
        if($this->isProcessed()){
            return self::STATE_PROCESSED;
        }
        $action = filter_input(INPUT_POST, 'action');
        if($action === 'create'){
            return self::STATE_FORM_SENT;
        }
        return self::STATE_FORM_REQUESTED;
    }
    private function readPost() : EmployeeModel{
        $employee = [];
        $employee['name'] = filter_input(INPUT_POST, 'name');
        $employee['surname'] = filter_input(INPUT_POST, 'surname');
        $employee['job'] = filter_input(INPUT_POST, 'job');
        $employee['wage'] = filter_input(INPUT_POST, 'wage');
        $employee['room'] = filter_input(INPUT_POST, 'rooms');
        $employee['password'] = filter_input(INPUT_POST, 'password');
        $employee['password'] = $this->Hash($employee['password']);
        $employee['admin'] = filter_input(INPUT_POST, 'admin', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return new EmployeeModel($employee); //!!
    }
    private function selectRoom() : array{
        $i = 0;
        $room = [];
        $stmt = $this->pdo->prepare("SELECT name, room_id FROM room");

        $stmt->execute();
        while($row = $stmt->fetch()){
            $room[$i] = $row;
            $i++;
        }

        return $room;
        //dumpe($this->rooms);
    }

    private function Hash($password) : string{
        $newPassword = password_hash("$password", PASSWORD_BCRYPT);
        return $newPassword;
    }

    private function createKey(int $employeeId) : void{
        $key = [];
        $r_keys = $_POST['keys'];
        if(!empty($r_keys)){
            for($i=0; $i<count($r_keys);$i++){
                $key['employee'][] = $employeeId;
                $key['room'][] = $r_keys[$i];
            }
        }
        for($i = 0; $i < count($key['employee']); $i++){
            $stmt = $this->pdo->prepare("INSERT INTO `key` (employee, room) VALUES ({$key['employee'][$i]},{$key['room'][$i]})");
            $stmt->execute();
        }
    }


}

$page = new CreateEmployeePage();
$page->render();

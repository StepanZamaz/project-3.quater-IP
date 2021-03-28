<?php
require "../includes/bootstrap.inc.php";



final class UpdateEmployeePage extends BaseCRUDPage{

    private EmployeeModel $employee;
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
            //HOTOVKa
            if($this->result === self::RESULT_SUCCESS){
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='5;url=./employeeList.php'>";
                $this->title = "Místnost upravena";
            }elseif($this->result === self::RESULT_FAIL){
                $this->title = "Upravení místnosti selhalo";
            }
        }
        else if ($this->state === self::STATE_FORM_SENT){
            //NAČÍST DATA,
            $this->employee=$this->readPost();
            // VALIDOVAT DATA,
            if($this->employee->isValid()){
                //ULOZIT A PRESMEROVAT
                $token = bin2hex( random_bytes(20) );

                if($this->employee->update()){
                    $this->deleteKey($this->employee->getPrimaryKey());
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
                $this->title="Aktualizovat místnost: Neplatný formulář";
            }
        }
        else{
            //PREJIT NA FORMULAR
            $this->title="Aktualizovat místnost";
            $this->rooms = $this->selectRoom();
            $employee_id = $this->findId();
            foreach ($this->loadKey($employee_id) as $item){
                for($i = 0; $i < count($this->rooms); $i++){
                    if($this->rooms[$i]['room_id'] == $item['room']){
                        $this->rooms[$i]['key'] = true;
                    }
                }
            }
            if(!$employee_id){
                throw new ReqExc(400);
            }
            $this->employee = EmployeeModel::getById($employee_id);
            if(!$this->employee){
                throw new ReqExc(404);
            }
        }

    }

    protected function body(): string
    {
        if($this->state === self::STATE_FORM_REQUESTED){
            return $this->m->render("employeeForm", ['update' => true, 'employee' => $this->employee,'rooms'=>$this->rooms]);
        }elseif ($this->state === self::STATE_PROCESSED){
            if($this->result === self::RESULT_SUCCESS){
                return $this->m->render("employeeSuccess",["message" => "Místnost byla úspěšně aktualizována."]);
            }elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("employeeFail",["message"=>"Aktualizace místnosti selhalo."]);
            }
        }
    }

    protected function getState() : int{
        //PROCESSED
        if ($this->isProcessed())
            return self::STATE_PROCESSED;

        $action = filter_input(INPUT_POST, 'action');
        if($action === 'update'){
            return self::STATE_FORM_SENT;
        }
        return self::STATE_FORM_REQUESTED;
    }
    private function findId() : ?int{
        $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);
        return $employee_id;
    }

    private function readPost() : EmployeeModel{
        $employee = [];
        $employee['employee_id'] = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        $employee['name'] = filter_input(INPUT_POST, 'name');
        $employee['surname'] = filter_input(INPUT_POST, 'surname');
        $employee['job'] = filter_input(INPUT_POST, 'job');
        $employee['wage'] = filter_input(INPUT_POST, 'wage');
        $employee['room'] = filter_input(INPUT_POST, 'rooms');
        $employee['password'] = filter_input(INPUT_POST, 'password');
        $employee['password'] = $this->Hash($employee['password']);
        $employee['admin'] = filter_input(INPUT_POST, 'admin', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        //dumpe($_POST);
        return new EmployeeModel($employee);
    }
    private function selectRoom() : array{
        $stmt = $this->pdo->prepare("SELECT * FROM room");
        $stmt->execute();
        $rooms = [];
        $index = 0;
        while($row = $stmt->fetch($this->pdo::FETCH_ASSOC)){
            $rooms[$index] = $row;
            $index++;
        }
        return $rooms;
    }
    private function Hash($password) : string{
        $newPassword = password_hash("$password", PASSWORD_BCRYPT);
        return $newPassword;
    }

    private function loadKey(int $employeeId) : array{
        $stmt = $this->pdo->prepare("SELECT employee, room FROM `key` WHERE employee = {$employeeId}");
        $stmt->execute();
        $keys = [];
        $index = 0;
        while($row = $stmt->fetch($this->pdo::FETCH_ASSOC)){
            $keys[$index] = $row;
            $index++;
        }
        return $keys;
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

    private function deleteKey(int $employeeId) : bool{
        $query = "DELETE FROM `key` WHERE employee = {$employeeId}";
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(":employee", $employeeId);

        return $stmt->execute();
    }
}

$page = new UpdateEmployeePage();
$page->render();

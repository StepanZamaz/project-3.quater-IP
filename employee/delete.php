<?php
require "../includes/bootstrap.inc.php";



final class DeleteEmployeePage extends BaseCRUDPage{

    private ?int $employee_id;

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
                $this->title = "Zaměstnanec smazán";
            }elseif($this->result === self::RESULT_FAIL){
                $this->title = "Smazání zaměstnance selhalo";
            }
        }
        else if($this->state === self::STATE_DELETE_REQUESTED) {
            //NAČÍST DATA,
            $this->employee_id = $this->readPost();
            $this->deleteKey($this->employee_id);
            // VALIDOVAT DATA,
            if (!$this->employee_id) {
                throw new ReqExc(400);
            }
            //smazat A PRESMEROVAT
            $token = bin2hex(random_bytes(20));

            if ( EmployeeModel::deleteById($this->employee_id)) {
                //uspech
                $this->sessionStorage->set($token, ['result' => self::RESULT_SUCCESS]);
//                 $this->redirect(self::RESULT_SUCCESS);
            } else {
                //neuspech
                $this->sessionStorage->set($token, ['result' => self::RESULT_FAIL]);
                //$this->redirect(self::RESULT_FAIL);
            }
            $this->redirect($token);
        }

    }

    protected function body(): string
    {
        if($this->result === self::RESULT_SUCCESS){
            return $this->m->render("employeeSuccess",["message" => "Zaměstnanec byl úspěšně smazán."]);
        }elseif ($this->result === self::RESULT_FAIL){
            return $this->m->render("employeeFail",["message"=>"Smazání zaměstnance selhalo."]);
        }
    }

    protected function getState() : int{
        //PROCESSED
        if($this->isProcessed()){
            return self::STATE_PROCESSED;
        }
        return self::STATE_DELETE_REQUESTED;
    }
    private function readPost() : ?int{
        $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        return $employee_id;
    }
    private function deleteKey(int $employeeId) : bool{
        $query = "DELETE FROM `key` WHERE employee = {$employeeId}";
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(":employee", $employeeId);

        return $stmt->execute();
    }
}

$page = new DeleteEmployeePage();
$page->render();

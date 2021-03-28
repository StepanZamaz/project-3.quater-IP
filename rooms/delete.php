<?php
require "../includes/bootstrap.inc.php";



final class DeleteRoomPage extends BaseCRUDPage{

    private ?int $room_id;

    protected function setUp(): void
    {
        if(!$_SESSION['isAdmin']){
            header("Location: ../rooms/roomList.php");
            exit;
        }
        if(!$_SESSION['loggedIn']){
            header("Location: ../rooms/roomList.php");
            exit;
        }
        parent::setUp();


        $this->state= $this->getState();

         if($this->state === self::STATE_PROCESSED){
             //HOTOVKa
             if($this->result === self::RESULT_SUCCESS){
                 $this->extraHeaders[] = "<meta http-equiv='refresh' content='5;url=./roomList.php'>";
                 $this->title = "Místnost smazána";
             }elseif($this->result === self::RESULT_FAIL){
                 $this->title = "Smazání místnosti selhalo";
             }
         }
         else if($this->state === self::STATE_DELETE_REQUESTED) {
             //NAČÍST DATA,
             $this->room_id = $this->readPost();
             // VALIDOVAT DATA,
             if (!$this->room_id) {
                 throw new ReqExc(400);
             }
             //smazat A PRESMEROVAT
             $token = bin2hex(random_bytes(20));

             if ( RoomModel::deleteById($this->room_id)) {
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
            return $this->m->render("roomSuccess",["message" => "Místnost byla úspěšně smazána."]);
        }elseif ($this->result === self::RESULT_FAIL){
            return $this->m->render("roomFail",["message"=>"Smazání místnosti selhalo."]);
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
        $room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        return $room_id;
    }


}

$page = new DeleteRoomPage();
$page->render();

<?php
require "../includes/bootstrap.inc.php";



final class UpdateRoomPage extends BaseCRUDPage{

    private RoomModel $room;

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
                 $this->title = "Místnost upravena";
             }elseif($this->result === self::RESULT_FAIL){
                 $this->title = "Upravení místnosti selhalo";
             }
         }
         else if ($this->state === self::STATE_FORM_SENT){
             //NAČÍST DATA,
                $this->room=$this->readPost();
             // VALIDOVAT DATA,
             if($this->room->isValid()){
                 //ULOZIT A PRESMEROVAT
                 $token = bin2hex( random_bytes(20) );

                 if($this->room->update()){
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
             $room_id = $this->findId();
             if(!$room_id){
                 throw new ReqExc(400);
             }
             $this->room = RoomModel::getById($room_id);
             if(!$this->room){
                 throw new ReqExc(404);
             }
         }

    }

    protected function body(): string
    {
        if($this->state === self::STATE_FORM_REQUESTED){
            return $this->m->render("roomForm", ['update' => true, 'room' => $this->room]);
        }elseif ($this->state === self::STATE_PROCESSED){
            if($this->result === self::RESULT_SUCCESS){
                return $this->m->render("roomSuccess",["message" => "Místnost byla úspěšně aktualizována."]);
            }elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("roomFail",["message"=>"Aktualizace místnosti selhalo."]);
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
        $room_id = filter_input(INPUT_GET, 'room_id', FILTER_VALIDATE_INT);
        return $room_id;
    }

    private function readPost() : RoomModel{
        $room = [];
        $room['room_id'] = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        $room['name'] = filter_input(INPUT_POST, 'name');
        $room['no'] = filter_input(INPUT_POST, 'no');
        $room['phone'] = filter_input(INPUT_POST, 'phone');
        if(!$room['phone'])
            $room['phone'] = null;

        return new RoomModel($room);
    }
}

$page = new UpdateRoomPage();
$page->render();

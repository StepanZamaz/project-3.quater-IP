<?php
require "../includes/bootstrap.inc.php";



final class CreateRoomPage extends BaseCRUDPage{
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
                 $this->title = "Místnost založena";
             }elseif($this->result === self::RESULT_FAIL){
                 $this->title = "Založení místnosti selhalo";
             }
         }
         else if($this->state === self::STATE_FORM_SENT){
             //NAČÍST DATA,
                $this->room=$this->readPost();
             // VALIDOVAT DATA,
             if($this->room->isValid()){
                 //ULOZIT A PRESMEROVAT
                 $token = bin2hex(random_bytes(20));
                 if($this->room->insert()){
                     //uspech
                     $this->sessionStorage->set($token, ['result' => self::RESULT_SUCCESS]);
                 } else {
                     //neuspech
                     $this->sessionStorage->set($token, ['result' => self::RESULT_FAIL]);
                 }
                 $this->redirect($token);


             }else{// JIT NA FORMULAR NEBO
                 $this->state = self::STATE_FORM_REQUESTED;
                 $this->title="Založit místnost: Neplatný formulář";
             }
         }
         else{
             //PREJIT NA FORMULAR
             $this->title="Založit místnost";
             $this->room = new RoomModel();
         }

    }

    protected function body(): string
    {
        if($this->state === self::STATE_FORM_REQUESTED){
            return $this->m->render("roomForm", ['create' => true ,'room' => $this->room]);
        }elseif ($this->state === self::STATE_PROCESSED){
            if($this->result === self::RESULT_SUCCESS){
                return $this->m->render("roomSuccess",["message" => "Místnost byla úspěšně vytvořena."]);
            }elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("roomFail",["message"=>"Vytvoření místnosti selhalo."]);
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
    private function readPost() : RoomModel{
        $room = [];
        $room['name'] = filter_input(INPUT_POST, 'name');
        $room['no'] = filter_input(INPUT_POST, 'no');
        $room['phone'] = filter_input(INPUT_POST, 'phone');
        if(!$room['phone'])
            $room['phone'] = null;

        return new RoomModel($room);
    }
}

$page = new CreateRoomPage();
$page->render();

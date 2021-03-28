<?php


final class EmployeeModel extends BaseModel
{


    protected string $dbTable = "employee";

    protected string $primaryKeyName = "employee_id";

    //seznam polozek
    protected array $dbKeys = ["name","surname","job","wage", "room","admin","password"];

    public string $name = "";
    public string $surname = "";
    public string $job = "";
    public string $wage = "";
    public string $room = "";
    public string $password = "";
    public bool $admin = false;

    public function isValid(): bool{
        if(!$this->name)
            return false;

        if(!$this->surname)
            return false;

        if(!$this->job)
            return false;

        if(!$this->wage)
            return false;

        if(!$this->password)
            return false;

        if(!$this->room)
            return false;

        return true;
    }
}
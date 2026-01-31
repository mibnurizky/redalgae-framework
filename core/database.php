<?php
namespace Amoeba\Core;
class Database{

    public $driver      = 'mysql';
    public $host        = '';
    public $port        = '3306';
    public $dbname      = '';
    public $username    = '';
    public $password    = '';
    public $instace     = '';

    public function __construct($instance='default'){
        $this->instace = $instance;
        $connection = $this->connectionlist();
        $this->driver = $connection['driver'];
        $this->host = $connection['host'];
        $this->port = $connection['port'];
        $this->dbname = $connection['dbname'];
        $this->username = $connection['username'];
        $this->password = $connection['password'];
    }

    public function connectionlist(){
        include ROOT_PATH.'/config/database.php';

        return $connection[$this->instace];
    }

    public function setInstance($instance){
        include ROOT_PATH.'/config/database.php';

        if(array_key_exists($instance,$connection)){
            $this->instace = $instance;
            $connection = $this->connectionlist();
            $this->driver = $connection['driver'];
            $this->host = $connection['host'];
            $this->port = $connection['port'];
            $this->dbname = $connection['dbname'];
            $this->username = $connection['username'];
            $this->password = $connection['password'];
            return true;
        }
        else{
            return false;
        }
    }

    public function connect($return=false){
        try{
            $conn = new \PDO($this->driver.':host='.$this->host.'; port='.$this->port.'; dbname='.$this->dbname,$this->username,$this->password);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        catch(\PDOException $e){
            if($return){
                return false;
            }
            echo "Connection error ".$e->getMessage();
            exit;
        }
    }

    public function getFields($table){
        $data = $this->select("DESCRIBE ".$table);
        return $data;
    }

    public function query($sql,$parameters=array(),&$lasterror=""){
        $conn = $this->connect();
        try{
            $query = $conn->prepare($sql);
            $query->execute((count($parameters) > 0 ? $parameters : null));
            return $query;
        }
        catch(\PDOException $e){
            $lasterror = $e->getMessage();
            return false;
        }
    }

    public function select($sql, $parameters=array(),&$lasterror=""){
        $data = $this->query($sql,$parameters,$lasterror);
        if(!$data){
            return false;
        }
        $arData = $data->fetchAll(\PDO::FETCH_ASSOC);
        return $arData;
    }

    public function insert($table,$fields,&$lasterror=""){
        $fields_db = $this->getFields($table);
        $arFields = array();
        foreach($fields_db as $row){
            $arFields[] = $row['Field'];
        }

        $arKey = array();
        $arValue = array();
        $arParams = array();
        foreach($fields as $key => $value){
            if(in_array($key,$arFields)){
                $arKey[] = $key;
                $arValue[] = ":".$key;
                $arParams[$key] = $value;
            }
        }

        if(count($arKey) > 0){
            $sql = "INSERT INTO ".$table." (".implode(",",$arKey).") VALUES (".implode(",",$arValue).")";
            return $this->query($sql,$arParams,$lasterror);
        }
        else{
            return false;
        }
    }

    public function update($table,$fields,$where,$parameters=array(),&$lasterror=""){
        $fields_db = $this->getFields($table);
        $arFields = array();
        foreach($fields_db as $row){
            $arFields[] = $row['Field'];
        }

        $arValueField = array();
        $arParams = array();
        foreach($fields as $key => $value){
            if(in_array($key,$arFields)){
                $arValueField[] =  " ".$key." = :FIELD_".$key." ";
                $arParams["FIELD_".$key] = $value;
            }
        }

        foreach($parameters as $key => $value){
            $arParams[$key] = $value;
        }

        if(count($arValueField) > 0){
            $sql = "UPDATE ".$table." SET ".implode(",",$arValueField)." ".$where;
            return $this->query($sql,$arParams,$lasterror);
        }
        else{
            return false;
        }

    }

    public function delete($table,$where,$parameters=array(),&$lasterror=""){
        $sql = "DELETE FROM ".$table." ".$where;
        return $this->query($sql,$parameters,$lasterror);
    }
}
?>
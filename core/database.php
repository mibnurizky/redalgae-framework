<?php
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
    }

    public function connectionlist(){
        $connection = array(
            'default' => array(
                'driver'        => 'mysql',
                'host'          => 'localhost',
                'port'          => '3307',
                'dbname'        => 'bitrix_kag',
                'username'      => 'root',
                'password'      => 'annonymous'
            )
        );

        return $connection[$this->instace];
    }

    public function connect(){
        $connection = $this->connectionlist();
        $this->driver = $connection['driver'];
        $this->host = $connection['host'];
        $this->port = $connection['port'];
        $this->dbname = $connection['dbname'];
        $this->username = $connection['username'];
        $this->password = $connection['password'];

        try{
            $conn = new PDO($this->driver.':host='.$this->host.'; port='.$this->port.'; dbname='.$this->dbname,$this->username,$this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        catch(PDOException $e){
            echo "Connection error ".$e->getMessage();
            exit;
        }
    }

    public function getFields($table){
        $data = $this->select("DESCRIBE ".$table);
        return $data;
    }

    public function query($sql,$parameters=array()){
        $conn = $this->connect();
        $query = $conn->prepare($sql);
        $query->execute((count($parameters) > 0 ? $parameters : null));
        return $query;
    }

    public function select($sql, $parameters=array()){
        $data = $this->query($sql,$parameters);
        $arData = $data->fetchAll(PDO::FETCH_ASSOC);
        return $arData;
    }

    public function insert($table,$fields){
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
            return $this->query($sql,$arParams,$index);
        }
        else{
            return false;
        }
    }

    public function update($table,$fields,$where,$parameters=array()){
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
            return $this->query($sql,$arParams,$index);
        }
        else{
            return false;
        }

    }

    public function delete($table,$where,$parameters=array()){
        $sql = "UPDATE ".$table." SET ".implode(",",$arValueField)." ".$where;
        return $this->query($sql,$parameters);
    }
}
?>
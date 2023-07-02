<?php

class Database{
    //DB config
    private $host = "127.0.0.1";
    private $dbname = "auth";
    private $user = "root";
    private $pass = "123456789";
    //PDO Object
    private $dbh;
    private $stmt;
    private $error;

    public function __construct(){
        //Set DataSourceName
        $dsn = 'mysql:host='.$this->host.';dbname='.$this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );
        //Create PDO instance
        try{
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    //Prepare statement
    public function query($sql){
        $this->stmt = $this->dbh->prepare($sql);
    }

    //Bind values to prepared statement
    public function bind($param, $value, $type = NULL){
        if(is_null ($type)){
            switch(true){
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    //Execute the prepared statement
    public function execute(){
        return $this->stmt->execute();
    }
        
    //Fetch all records
    public function fetchAll(){
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    //Fetch single record
    public function fetch(){
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    //Get row count
    public function rowCount(){
        return $this->stmt->rowCount();
    }
}
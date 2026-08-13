<?php
class DB{
    private $conn;

    public function connectDB(){
        $this->conn = new PDO(
        "mysql:host=db;dbname=my_database;charset=utf8",
        "dbuser",
        "dbpassword"
    );

    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $this->conn;
    }
}

?>

<?php

namespace MySQLDbOps;

use PDO, PDOException;

class DbConnection
{
    private string $host;
    private string $userName;
    private string $password;
    private string $dbName;

    protected function __construct(string $host, string $userName, string $password, string $dbName)
    {
        $this->host = $host;
        $this->userName = $userName;
        $this->password = $password;
        $this->dbName = $dbName;
    }

    protected function connect(): ?PDO
    {
        try {
            $conn = new PDO("mysql:host=$this->host;dbname=$this->dbName", $this->userName, $this->password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//            echo "Connected successfully";
            return $conn;
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return null;
        }
    }
}

<?php
date_default_timezone_set('Asia/Calcutta');
define('CLIENT_ID', 1);

class database
{
    // private $host = "localhost";
    // private $dbname = "fruvive";
    // private $username = "root";
    // private $password = "";
    private $host = "sql103.infinityfree.com";
    private $username = "if0_42481947";
    private $password = "YOUR_INFINITYFREE_PASSWORD";
    private $dbname   = "if0_42481947_fruvive";

    public $conn;

    public function __construct()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $error) {
            die("DB Error: " . $error->getMessage());
        }
    }
}

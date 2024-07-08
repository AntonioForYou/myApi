<?php

class Database
{
    private static $instance = null;

    private $connection = null;

    private function __construct()
    {
        $this->connection = new PDO("mysql:dbname=my_api;host=localhost","root","",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new BadMethodCallException('Unable to deserialize database connection');
    }

    public static function getInstance()
    {
        if(null === self::$instance)
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public static function connection()
    {
        return self::getInstance()->connection;
    }
}
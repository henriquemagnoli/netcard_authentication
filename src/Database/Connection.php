<?php

namespace NetCard\Database;

use PDO;

class Connection
{
    private static $openConnection;

    public static function openConnection()
    {
        if(self::$openConnection === null)
        {
            self::$openConnection = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8',
                                            $_ENV['DB_USERNAME'],
                                            $_ENV['DB_PASSWORD']);

            self::$openConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$openConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$openConnection;
    }

    public static function closeConnection()
    {
        return null;
    }
}

?>
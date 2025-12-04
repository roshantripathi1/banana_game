<?php
// services/Database.php
// Creates and returns one shared PDO connection.

class Database
{
    // Holds PDO instance
    private static ?PDO $conn = null;

    // Get a PDO connection
    public static function getConnection(): PDO
    {
        if (self::$conn === null) {
            $host = "localhost";
            $dbname = "banana_game";
            $user = "root";
            $pass = ""; // change if you have a password

            self::$conn = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass
            );
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$conn;
    }
}

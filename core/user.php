<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/myApi/core/database.php");

class User
{
    public static function addUser(array $data): void
    {
        $validateCode = User::validateAddUser($data);

        switch ($validateCode)
        {
            case 0:
            {
                $pdo = Database::connection()->prepare("INSERT INTO `users` (`email`, `username`, `password`) 
                VALUES (:email, :username, :password)");

                $pdo->bindParam(":username", $data["username"], PDO::PARAM_STR);
                $pdo->bindParam(":email", $data["email"], PDO::PARAM_STR);

                $hash_password = hash("sha256", $data["password"]);

                $pdo->bindParam(":password", $hash_password, PDO::PARAM_STR);

                if($pdo->execute())
                {
                    http_response_code(200);

                    echo json_encode([
                        "status"=> true,
                        "message"=> "User with email " . $data["email"] . " successfully added"
                    ]);
                }
                else
                {
                    http_response_code(501);

                    echo json_encode([
                        "status"=> false,
                        "message"=> "Error adding: " . $pdo->errorInfo()[2]
                    ]);
                }

                break;
            }

            case 1:
            {
                http_response_code(200);

                echo json_encode([
                    "status"=> false,
                    "message"=> "User with such email already exists"
                ]);

                break;
            }

            case 2:
            {
                http_response_code(200);

                echo json_encode([
                    "status"=> false,
                    "message"=> "Not enough data"
                ]);
            }
        }
    }

    private static function validateAddUser(array $data): int
    {
        if(isset($data["email"]) and $data["username"] and isset($data["password"]))
        {
            $user = User::getUserByEmail($data["email"]);

            if(!empty($user))
            {
                return 1;
            }

            return 0;
        } 
        else
        {
            return 2;
        }
    }

    private static function getUserByEmail(string $email): array
    {
        $pdo = Database::connection()->prepare("SELECT `email`, `username` FROM `users` WHERE `email` = :email");

        $pdo->bindParam(":email", $email, PDO::PARAM_STR);

        $pdo->execute();

        $user = $pdo->fetch(PDO::FETCH_ASSOC);

        if(empty($user))
            return [];
        else
            return $user;
    }

    public static function getUserInfo(string $email)
    {
        $user = User::getUserByEmail($email);

        if(!empty($user))
        {
            http_response_code(200);

            echo json_encode($user);
        }
        else
        {
            http_response_code(404);

            echo json_encode([
                "status"=> false,
                "message"=> "User with such email does not exist"
            ]);
        }
    }

    public static function getInfoAllUsers()
    {
        $pdo = Database::connection()->query("SELECT `email`, `username` from `users`");

        $users = $pdo->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);

        echo json_encode($users);
    }

    public static function updateUser(string $email, array $data)
    {
        $user = User::getUserByEmail($email);

        if(!empty($user))
        {
            if(isset($data["username"]) && !empty($data["password"]))
            {
                $pdo = Database::connection()->prepare("UPDATE `users` SET `username` = :username,
                `password` = :password WHERE `email` = :email");

                $hash_password = hash("sha256", $data["password"]);

                $pdo->bindParam(":username", $data["username"], PDO::PARAM_STR);
                $pdo->bindParam(":password", $hash_password, PDO::PARAM_STR);
                $pdo->bindParam(":email", $email, PDO::PARAM_STR);

                if($pdo->execute())
                {
                    http_response_code(201);

                    echo json_encode([
                        "status" => true,
                        "message"=> "User with email " . $email . " updated"
                    ]);
                }
                else
                {
                    http_response_code(501);

                    echo json_encode([
                        "status"=> false,
                        "message"=> "User has not been updated"
                    ]);
                }

            }
            else
            {
                http_response_code(200);

                echo json_encode([
                    "status"=> false,
                    "message"=> "Not enough data"
                ]);

            }
        }
        else
        {
            http_response_code(404);

            echo json_encode([
                "status"=> false,
                "message"=> "User with such email does not exist"
            ]);
        }
    }

    public static function deleteUser(string $email)
    {
        $user = User::getUserByEmail($email);

        if(!empty($user))
        {
            $pdo = Database::connection()->prepare("DELETE FROM `users` WHERE `email` = :email");

            $pdo->bindParam(":email", $email, PDO::PARAM_STR);

            if($pdo->execute())
            {
                http_response_code(410);

                echo json_encode([
                    "status"=> true,
                    "message"=> "User with email " . $email . " has been deleted" 
                ]);
            }
            else
            {
                http_response_code(501);
                
                echo json_encode([
                    "status"=> false,
                    "message"=> "User has not been deleted"
                ]);
            }
        }
        else
        {
            http_response_code(404);

            echo json_encode([
                "status"=> false,
                "message"=> "User with such email does not exist"
            ]);
        }
    }

    public static function authorization(array $data)
    {
        if(isset($data["email"]) and isset($data["password"]))
        {
            $user = User::getUserByEmail($data["email"]);

            if(!empty($user))
            {
                $pdo = Database::connection()->prepare("SELECT `password` FROM `users` WHERE `email` = :email");
                
                $pdo->bindParam(":email", $data["email"], PDO::PARAM_STR);

                $pdo->execute();

                $hash_pass_in_db = $pdo->fetchColumn();

                $hash_paasword = hash("sha256", $data["password"]);

                if($hash_pass_in_db === $hash_paasword)
                {
                    User::getUserInfo($data["email"]);
                }
                else
                {
                    echo json_encode([
                        "status"=> false,
                        "message"=> "Password wrong"
                    ]);
                }
                
            }
            else
            {
                http_response_code(404);

                echo json_encode([
                    "status"=> false,
                    "message"=> "User with such email does not exist"
                ]);
            }
        }
        else
        {
            http_response_code(200);

                echo json_encode([
                    "status"=> false,
                    "message"=> "Not enough data"
                ]);
        }
    }
}


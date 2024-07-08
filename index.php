<?php
header("Content-type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Credentials: true");

require_once($_SERVER['DOCUMENT_ROOT'] . "/myApi/core/user.php");

$error_response = json_encode([
    "status" => false,
    "message"=> "No such resource"
]);

if(!isset($_GET["q"])) 
    die($error_response);

$method = $_SERVER["REQUEST_METHOD"];

$queryParts = explode("/", $_GET["q"]);

if($queryParts[0] === "users")
{
    switch ($method) 
    {
        case "GET":
        {
            if(isset($queryParts[1]))
            {
                User::getUserInfo($queryParts[1]);
            }
            else
            {
                User::getInfoAllUsers();
            }

            break;
        }

        case "POST":
        {
            $data = file_get_contents("php://input");
            $data = json_decode($data, true);

            if(isset($queryParts[1]))
            {
                if($queryParts[1] === "auth" && !isset($queryParts[2]))
                {
                    User::authorization($data);
                }
                else
                {
                    die($error_response);
                }
            }
            else
            {
                User::addUser($data);
            }

            break;
        }

        case "PATCH":
        {
            if(isset($queryParts[1]))
            {
                $data = file_get_contents("php://input");
                $data = json_decode($data, true);

                User::updateUser($queryParts[1], $data);
            }

            break;
        }

        case "DELETE":
        {
            if(isset($queryParts[1]))
            {
                User::deleteUser($queryParts[1]);
            }

            break;
        }
            
    }
}
else
{
    die($error_response);
}




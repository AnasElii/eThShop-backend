<?php

require_once "./dbConnection.php";

// Read raw POST data
$inputJSON = file_get_contents('php://input');

// Decode JSON data
$inputData = json_decode($inputJSON, true);

// Register the user
if (isset($inputData['submit'])) {
    $type = $inputData['type'];
    
    if($type === "register"){
        $username = $inputData['username'];
        $email = $inputData['email'];
        $tel = $inputData['tel'];
        $password = password_hash($inputData['password'], PASSWORD_DEFAULT);
        $user = register($username, $email,  "0600000000", $password);

        if ($user) {
            session_start();
            $_SESSION['user'] = $user;
            $userID = $_SESSION['user'][0]['id'];

            echo json_encode([
                'status' => 'success',
                'type' => 'register',
                'message' => 'Register in successfully',
                'time' => date("Y-m-d h:m:s"),
                'id' => json_encode($userID),
                'username' => $username
            ]);

        } else {

            echo json_encode([
                'status' => 'error',
                'type' => 'register',
                'time' => date("Y-m-d h:m:s"),
                'message' => 'Wrong credentials'
            ]);

        }
    }

    if($type === "role"){
        $username = $inputData['username'];
        $role = $inputData['role'];
        $user = role($username, $role);

        if ($user) {
            session_start();
            $_SESSION['user'] = $user;

            echo json_encode([
                'status' => 'success',
                'type' => 'role',
                'message' => 'role in successfully',
                "time" => date("Y-m-d h:m:s"),
                'username' => $username,
                'role' => 1
            ]);

        } else {

            echo json_encode([
                'status' => 'error',
                'type' => 'role',
                'time' => date("Y-m-d h:m:s"),
                'message' => 'Wrong credentials'
            ]);

        }
    }
}

function register($username, $email, $tel, $password)
{
    // Create Database Object
    $pdo = new Database();

    // Create Query
    $query = "INSERT INTO users (username, email, tel, password) VALUES(:username, :email, :tel, :password)";
    $array = [];
    array_push($array, $username);
    array_push($array, $email);
    array_push($array, $tel);
    array_push($array, $password);

    // Send Request
    $user = $pdo->execQuery($query, $array);

    // Get the user
    if ($user != null) {
        
        // Create Query
        $query = "SELECT * FROM users WHERE email = :email";
        $array = [];
        array_push($array, $email);

        // Send Request and Ger the user
        $user = $pdo->execQuery($query, $array);

        // return the user
        return $user;
    }

    return false;
}

function role($username, $role){
    // Create Database Object
    $pdo = new Database();

    // Create Query
    $query = "UPDATE users SET role=:role WHERE username = :username";
    $array = [];
    array_push($array, $role);
    array_push($array, $username);

    // Send Request
    $user = $pdo->execQuery($query, $array);

    // Get the user
    if ($user != null) {
        
        // Create Query
        $query = "SELECT * FROM users WHERE username = :username";
        $array = [];
        array_push($array, $username);

        // Send Request and Ger the user
        $user = $pdo->execQuery($query, $array);

        // return the user
        return $user;
    }

    return false;

}
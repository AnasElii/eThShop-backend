<?php //login.php

require_once "./dbConnection.php";

// Start the session
session_start();

// Read raw POST data
$inputJSON = file_get_contents('php://input');

// Decode JSON data
$inputData = json_decode($inputJSON, true);

// Login the user
if (isset($inputData['submit'])) {

    $username = $inputData['username'];
    $password = $inputData['password'];
    $user = login($username, $password);

    if ($user) {

        // Store user data in the session
        $_SESSION['user'] = $user;

        // Return the user data as a response
        $userID = $_SESSION['user'][0]['id'];
        $userUsername = $_SESSION['user'][0]['username'];

        echo json_encode([
            'status' => 'success',
            'type' => 'login',
            'message' => 'Logged in successfully',
            "login time" => date("Y-m-d h:m:s"),
            'id' => json_encode($userID),
            'username' => $userUsername
        ]);
    } else {

        echo json_encode([
            'status' => 'error',
            'type' => 'login',
            "error time" => date("Y-m-d h:m:s"),
            'message' => 'Wrong credentials'
        ]);
    }
}

// Function to login a user
function login($username, $password)
{
    $pdo = new Database();
    $query = "SELECT * FROM users WHERE username = :username";
    $array = [];
    array_push($array, $username);
    $user = $pdo->execQuery($query, $array);
    // echo json_encode($user);

    if ($user && password_verify($password, $user[0]['password'])) {
        return $user;
    }

    return false;
}

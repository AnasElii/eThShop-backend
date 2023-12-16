<?php

//get the current session
session_start();

// Read raw POST data
$inputJSON = file_get_contents('php://input');

// Decode JSON data
$inputData = json_decode($inputJSON, true);

// Login the user
if (isset($inputData['submit'])) 
{
    // $user will be null if no session is available; otherwise it will contain user data.
    $user = $_SESSION['user'][0] ?? null;

    //check if the user is logged in
    if ($user !== null) {

        $username = $user["username"];

        //return the username as a response
        echo json_encode([
            'status' => 'success',
            'type' => 'index',
            'message' => 'User is logged in',
            'data' => $username
        ]);

    } else {
        //return a message to the user
        echo json_encode([
            'status' => 'error',
            'type' => 'index',
            'message' => 'User is not logged in'
        ]);
    }
}else{
    echo json_encode([
        'status' => 'error',
        'type' => 'index',
        'message' => 'No Message'
    ]);
}

?>
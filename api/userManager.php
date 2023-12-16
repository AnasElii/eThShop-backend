<?php //login.php

require_once "./dbConnection.php";
require_once "./message.php";

// Start the session
session_start();

// Read raw POST data
$inputJSON = file_get_contents('php://input');

// Decode JSON data
$inputData = json_decode($inputJSON, true);

// Login the user
if (isset($inputData['submit'])) {

    $type = $inputData['type'];
    $message = new Messages();

    // Get User Data
    if ($type === "fetchUserData") {

        $id = $inputData['id'];
        $result = getUser($id);

        if ($result[0]) {
            $user = $result[1];
            $productCount = getProductCount($id);

            if ($productCount[0]) {
                
                $new_key = array('count');

                foreach ($productCount[1] as $index => $record) {
                    $productCount[1][$index] = array_combine($new_key, $record);
                }
                
                $user[0]['productCount'] = $productCount[1][0];
            
            } else {
            
                $user[0]['productCount'] = 0;
            
            }

            // Store user data in the session
            $_SESSION['user'] = $user;

            $message->StatusMessage('success', 'User Manager', 'User Data retrieved successfully', $user);
        } else {

            $message->StatusMessage('error', 'User Manager', 'Error in retrieving User Data');
        }
    }

    // Get Product Number
    if ($type === "productCount") {
        $id = $inputData['id'];

        // ==========
        $pdo = new Database();

        // Create Query
        $query = "SELECT * FROM products WHERE idUser = :id";
        $array = [];
        array_push($array, $id);

        // Send Request and Ger the user
        $productCount = $pdo->Query($query, $array);
        // ==========

        //$productCount = getProductCount($idUser);

        echo json_encode([
            'status' => 'success',
            'type' => 'user',
            "time" => date("Y-m-d h:m:s"),
            'message' => 'Product number',
            'id' => json_encode($id),
            'data' => json_encode($productCount)
        ]);
    }

    if ($type === "updateUser") {
        $id = $inputData['id'];
        $feild = $inputData['feild'];
        $data = $inputData['data'];
        $oldPassword = "";

        if ($feild === "password")
            $oldPassword = $inputData['oldPassword'];

        $result = updateUser($id, $feild, $data, $oldPassword);

        if ($result > 0) {

            echo json_encode([
                'status' => 'success',
                'type' => 'user',
                "time" => date("Y-m-d h:m:s"),
                'message' => 'User ' . $feild . ' update successfully',
                "feild" => $feild . ""
            ]);
        } else {

            echo json_encode([
                'status' => 'error',
                'type' => 'user',
                "error time" => date("Y-m-d h:m:s"),
                'message' => 'Error in updating User ' . $feild
            ]);
        }
    }

} else {

    echo json_encode([
        'status' => 'error',
        'type' => 'user',
        "time" => date("Y-m-d h:m:s"),
        'message' => 'Wrong credentials'
    ]);

}

//MARK: - USER MANAGER CRUD FUNCTIONS
// Function to get the user data
function getUser($id)
{
    $pdo = new Database();

    // Create Query
    $query = "SELECT 
                u.id, 
                u.username, 
                u.email, 
                u.tel, 
                u.role, 
                u.valid, 
                ui.path as imagePath 
            FROM users u 
            INNER JOIN userImages ui ON u.id = ui.idUser 
            WHERE ui.active = 1 AND u.id = :id";
    $array = [
        ':id' => $id
    ];

    // Send Request and Ger the user
    $user = $pdo->execQuery($query, $array);

    if ($user)
        return [true, $user];

    return [false, 'User not found'];
}

function updateUser($id, $feild, $data, $oldPassword)
{

    $pdo = new Database();
    $query = "";
    $array = [];

    // Update Username or Email or Telephone Number
    if ($feild === "username" || $feild === "email" || $feild === "tel") {

        $query = "UPDATE users SET $feild = :data WHERE id = :id";
        array_push($array, $data);
        array_push($array, $id);
    }

    // Update Password
    if ($feild === "password") {

        if (!checkPassword($id, $oldPassword))
            return false;

        $password = password_hash($data, PASSWORD_DEFAULT);
        $query = "UPDATE users SET $feild = :password WHERE id = :id";
        array_push($array, $password);
        array_push($array, $id);
    }

    // Send Request and Ger the user
    $result = $pdo->Query($query, $array);

    // return User data
    return $result;
}

function deleteUser()
{
}

function disableUser()
{
}

function enableUser()
{
}

function getProductCount($idUser)
{
    $pdo = new Database();

    // Create Query
    $query = "SELECT COUNT(*) FROM products WHERE idUser = :idUser";
    $array = [
        ':idUser' => $idUser
    ];

    // Send Request and Ger the user
    $result = $pdo->execQuery($query, $array);

    if ($result)
        return [true, $result];

    return [false, 'User has no products'];
}

function checkPassword($id, $oldPassword)
{

    $pdo = new Database();
    $query = "SELECT * FROM users WHERE id = :id";
    $array = [];
    array_push($array, $id);

    // Send Request and Ger the user
    $user = $pdo->execQuery($query, $array);


    if ($user && password_verify($oldPassword, $user[0]['password'])) {
        return true;
    }

    return false;
}
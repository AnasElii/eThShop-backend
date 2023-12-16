<?php

require_once 'dbConnection.php';
require_once 'message.php';


if (isset($_POST["submit"])) {
    $message = new Messages();

    $type = $_POST["type"];

    if ($type == "isFollowed") {

        // Get POST Data
        $idSeller = $_POST["idSeller"];
        $idUser = $_POST["idUser"];

        $isFollowed = isFollowed($idUser, $idSeller);

        // Send Data As a Json file
        if ($isFollowed)
            echo json_encode([
                'status' => 'success',
                'source' => 'likeManager',
                'type' => 'add like',
                'message' => 'Product liked successfully',
                'idFollowed' => true
            ]);
        else
            echo json_encode([
                'status' => 'failed',
                'source' => 'likeManager',
                'type' => 'add like',
                'message' => 'add like failed',
                'idFollowed' => false
            ]);

    }

    if ($type == "followersNumer") {
        $idSeller = $_POST['idSeller'];

        $result = followersNumer($idSeller);

        if ($result) {

            $new_key = array('count');

            foreach ($result as $index => $record) {
                $result[$index] = array_combine($new_key, $record);
            }

            $message->StatusMessage('success', 'Product Manager', 'Product count fetched successfully', $result);
        } else {
            $message->StatusMessage('error', 'Product Manager', 'Error fetching product count');
        }
    }

    if ($type == "add") {

        // Get POST Data
        $idSeller = $_POST["idSeller"];
        $idUser = $_POST["idUser"];

        $result = addFollow($idUser, $idSeller);

        // Send Data As a Json file
        if ($result)
            echo json_encode([
                'status' => 'success',
                'source' => 'followManager',
                'type' => 'add follow',
                'message' => 'Seller followed successfully',
            ]);
        else
            echo json_encode([
                'status' => 'failed',
                'source' => 'followManager',
                'type' => 'add follow',
                'message' => 'add follow failed',
            ]);
    }

    if ($type == "delete") {

        // Get POST Data
        $idSeller = $_POST["idSeller"];
        $idUser = $_POST["idUser"];

        $result = deleteFollow($idUser, $idSeller);

        // Send Data As a Json file
        if ($result)
            echo json_encode([
                'status' => 'success',
                'source' => 'followManager',
                'type' => 'delete follow',
                'message' => 'seller unFollowed successfully',
            ]);
        else
            echo json_encode([
                'status' => 'failed',
                'source' => 'followManager',
                'type' => 'delete follow',
                'message' => 'delete follow failed',
            ]);
    }


} else {
    echo json_encode([
        'status' => 'error',
        'type' => 'fetchProducts',
        'message' => 'Error fetching products'
    ]);
}

function isFollowed($idUser, $idSeller)
{

    // Open Connection with DB
    $conn = new Database();

    // Add liked Product 
    $query = "SELECT id FROM followers WHERE idFollower = :idUser AND idFollowed = :idSeller";
    $array = [];
    array_push($array, $idUser);
    array_push($array, $idSeller);
    return $conn->execQuery($query, $array);

}

function followersNumer($idSeller)
{

    // Open Connection with DB
    $conn = new Database();

    // Add liked Product 
    $query = "SELECT COUNT(*) FROM followers WHERE idFollowed = :idSeller";
    $array = [];
    array_push($array, $idSeller);
    return $conn->execQuery($query, $array);

}

function addFollow($idUser, $idSeller)
{

    // Open Connection with DB
    $conn = new Database();

    // Add liked Product 
    $query = "INSERT INTO followers(idFollower, idFollowed) VALUES(:idUser, :idSeller)";
    $array = [];
    array_push($array, $idUser);
    array_push($array, $idSeller);
    return $conn->execQuery($query, $array);


}

function deleteFollow($idUser, $idSeller)
{

    // Open Connection with DB
    $conn = new Database();

    // Add liked Product 
    $query = "DELETE FROM followers WHERE idFollower = :idUser AND idFollowed = :idSeller";
    $array = [];
    array_push($array, $idUser);
    array_push($array, $idSeller);
    return $conn->execQuery($query, $array);

}
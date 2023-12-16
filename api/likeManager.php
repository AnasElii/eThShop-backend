<?php

require_once 'dbConnection.php';


if (isset($_POST["submit"])) 
{   
    $type = $_POST["type"];
    
    if($type == "add"){
        // Get POST Data
        $idProduct = $_POST["idProduct"];
        $idUser = $_POST["idUser"];

        $isLiked = addLike($idUser, $idProduct);

        // Send Data As a Json file
        if($isLiked)
            echo json_encode([
                'status' => 'success',
                'source' => 'likeManager',
                'type' => 'add like',
                'message' => 'Product liked successfully',
            ]); 
        else
            echo json_encode([
                'status' => 'failed',
                'source' => 'likeManager',
                'type' => 'add like',
                'message' => 'add like failed',
            ]);   
    }

    if($type == "delete"){
        // Get POST Data
        $idProduct = $_POST["idProduct"];
        $idUser = $_POST["idUser"];

        $isLiked = deleteLike($idUser, $idProduct);

        // Send Data As a Json file
        if($isLiked)
            echo json_encode([
                'status' => 'success',
                'source' => 'likeManager',
                'type' => 'delete like',
                'message' => 'Product liked successfully',
            ]); 
        else
            echo json_encode([
                'status' => 'failed',
                'source' => 'likeManager',
                'type' => 'delete like',
                'message' => 'delete like failed',
            ]);   
    }
   
    
}else{
    echo json_encode([
        'status' => 'error',
        'type' => 'fetchProducts',
        'message' => 'Error fetching products'
    ]);
}

function addLike($idUser, $idProduct){

    // Open Connection with DB
    $conn = new Database();

    // Add liked Product 
    $query = "INSERT INTO likes(idUser, idProduct) VALUES(:idUser, :idProduct)";
    $array = [];
    array_push($array, $idUser);
    array_push($array, $idProduct);
    $isLiked = $conn->execQuery($query, $array);
    return $isLiked;
    

}

function deleteLike($idUser, $idProduct){

    // Open Connection with DB
    $conn = new Database();

    // Add liked Product 
    $query = "DELETE FROM likes WHERE idUser = :idUser AND idProduct = :idProduct";
    $array = [];
    array_push($array, $idUser);
    array_push($array, $idProduct);
    $isLiked = $conn->execQuery($query, $array);
    return $isLiked;
    
}
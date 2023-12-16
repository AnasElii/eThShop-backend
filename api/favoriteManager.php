<?php

require_once 'dbConnection.php';

if (isset($_POST["submit"])) 
{   
    $type = $_POST["type"];

    if($type == "fetch"){

        // Get POST Data
        $idUser = $_POST["idUser"];
        
        // Get Favorite List
        $data = fetchFavorite($idUser);

        // Fetch Liked Products From DB
        $likeList = fetchLikes($idUser);
        
        // Fetch User Products From DB
        $userList = fetchUserProducts($idUser);
        
        
        echo json_encode([
            'status' => 'success',
            'source' => 'likeManager',
            'type' => 'add like',
            'message' => 'Product liked successfully',
            'likedList' => $likeList,
            'userList' => $userList,
            'data' => $data,
            'idUser' => $idUser
        ]); 
   
    }
    
    if($type == "add"){
        
        // Get POST Data
        $idProduct = $_POST["idProduct"];
        $idUser = $_POST["idUser"];
        
        // Get Result From The Server
        $result = addFavorite($idUser, $idProduct);

        // Send Data As a Json file
        if($result)
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

        // Get Result From The Server
        $result = deleteFavorite($idUser, $idProduct);

        // Send Data As a Json file
        if($result)
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

function fetchFavorite($idUser){

    // Open Connection with DB
    $conn = new Database();

    // Fetch Favorite Products From DB
    $query = "SELECT 
            p.id, 
            p.name, 
            p.price, 
            p.sold, 
            p.location, 
            p.dateAdd, 
            p.bestseller, 
            p.visible, 
            p.idUser,
            i.path 
        FROM products p
        INNER JOIN images i ON p.id = i.idProduct 
        INNER JOIN favorites f ON p.id = f.idProduct
        WHERE f.idUser = :idUser
        AND i.id IN (SELECT MIN(id) FROM images GROUP BY idProduct)
        ORDER BY f.id DESC";
    $array = [];
    array_push($array, $idUser);
    $data = $conn->execQuery($query, $array);
    return $data;
}

function fetchLikes($idUser){

    // Open Connection with DB
    $conn = new Database();

    // Execute Query
    $query = "SELECT
                * 
            FROM likes
            WHERE idUser = :idUser";

    // get Data From Server
    $array = [];
    array_push($array, $idUser);

    // return result
    return $conn->execQuery($query, $array);
}

function fetchUserProducts($idUser){

    // Open Connection with DB
    $conn = new Database();

    $query = "SELECT id FROM products WHERE idUser = :idUser";
    $array = [];
    array_push($array, $idUser);
    $userList = $conn->execQuery($query, $array);

    return $userList;
}


function addFavorite($idUser, $idProduct){

    // Open Connection with DB
    $conn = new Database();

    // Add liked Product 
    $query = "INSERT INTO favorites(idUser, idProduct) VALUES(:idUser, :idProduct)";
    $array = [];
    array_push($array, $idUser);
    array_push($array, $idProduct);
    $result = $conn->execQuery($query, $array);
    return $result;
    
}

function deleteFavorite($idUser, $idProduct){

    // Open Connection with DB
    $conn = new Database();

    // Add liked Product 
    $query = "DELETE FROM favorites WHERE idUser = :idUser AND idProduct = :idProduct";
    $array = [];
    array_push($array, $idUser);
    array_push($array, $idProduct);
    $result = $conn->execQuery($query, $array);
    return $result;
    
}
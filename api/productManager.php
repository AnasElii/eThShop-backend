<?php

// DEBUGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

//GER CURRENT SESSIOn
session_start();

// INCLUDING HEADERS
require_once "dbConnection.php";
require_once "message.php";

if (isset($_POST["submit"])) 
{   
    $message = new Messages();
    
    $type = $_POST["type"];

    if($type == "fetch"){

        // Get POST Data
        $idProduct = $_POST["idProduct"];
        $idUser = $_POST["idUser"];

        // Add productd Product 
        $query = "SELECT * FROM favorites WHERE idUser = :idUser AND idProduct = :idProduct";
        $array = [];
        array_push($array, $idUser);
        array_push($array, $idProduct);
        $result = $conn->execQuery($query, $array);
        return $result;
    }
    
    if($type == "add"){
        
        // Get POST Data
        $idProduct = "";
        $idUser = $_POST["idUser"];
        $name = $_POST["name"];
        $price = $_POST["price"];
        $promo = $_POST["promo"];
        $location = "Rabat";
        $description = $_POST["description"];
        $category = "";
        $state = $_POST["state"];

        // Add productd Product
        $idProduct = addProduct($idUser, $name, $price, $promo, $location, $description, $state);
        
        if($idProduct){
            $message->StatusMessage('success', 'Product Manager', 'Product added successfully', $idProduct);
        }else{
            $message->StatusMessage('error', 'Product Manager', 'Error adding product');
        }

    }

    if($type == "update"){

        // Get POST Data
        $idProduct = $_POST['idProduct'];
        $name = $_POST["name"];
        $price = $_POST["price"];
        $promo = $_POST["promo"];
        $location = "Rabat";
        $description = $_POST["description"];
        $state = $_POST["state"];

        $result = updateProduct($name, $price, $promo, $location, $description, $state, $idProduct);
        if($result){
            $message->StatusMessage('success', 'Product Manager', 'Product updated successfully');
        }else{
            $message->StatusMessage('error', 'Product Manager', 'Error updating product');
        }
    }

    if($type == "hide"){

        // Get POST Data
        $visible = $_POST["visible"];
        $idProduct = $_POST["idProduct"];

        // Get Result From The Server
        $result = hideProduct($visible, $idProduct);

        // Send Data As a Json file
        if($result)
            $message->StatusMessage('success', 'Product Manager', 'Product Hide successfully');
        else
            $message->StatusMessage('error', 'Product Manager', 'Error Hide failed');
    }

    if($type == "delete"){

        // Get POST Data
        $idUser = $_POST["idUser"];
        $idProduct = $_POST["idProduct"];

        // Get Result From The Server
        $result = deleteProduct($idUser, $idProduct);

        // Send Data As a Json file
        if($result)
            $message->StatusMessage('success', 'Product Manager', 'Product deleted successfully');
        else
            $message->StatusMessage('error', 'Product Manager', 'Error deleting product');
               
    }
   
}else{
    $message = new Messages();
    $message->StatusMessage('error', 'Product Manager', "Page Not Found");
}

// Add product
function addProduct($idUser, $name, $price, $promo, $location, $description, $state){

    // Open Connection with DB
    $conn = new Database();

    // Add productd Product 
    $query = "INSERT INTO products (name, description, price, sold, location, dateAdd, state, idUser) VALUES (:name, :description, :price, :promo, :location, :dateAdd, :state, :idUser)";

    $array = [
        ':name' => $name,
        ':price' => $price,
        ':promo' => $promo,
        ':location' => $location,
        ':description' => $description,
        ':state' => $state,
        ':dateAdd' => date("Y-m-d H:i:s"),
        ':idUser' => $idUser
    ];
    $idProduct = $conn->execBindQuery($query, $array);
    return $idProduct;
}

// Update Product
function updateProduct($name, $price, $promo, $location, $description, $state, $idProduct){

    // Open Connection with DB
    $conn = new Database();

    // Update Product 
    $query = "UPDATE products SET name = :name, price = :price, sold = :promo, location = :location, description = :description, state = :state WHERE id = :idProduct";
    $array = [
        ':name' => $name,
        ':price' => $price,
        ':promo' => $promo,
        ':location' => $location,
        ':description' => $description,
        ':state' => $state,
        ':idProduct' => $idProduct
    ];
    $result = $conn->execQuery($query, $array);
    return $result;
}

function hideProduct($visible, $idProduct){
    // Open Connection with DB
    $conn = new Database();

    // Update Product 
    $query = "UPDATE products SET visible = :visible WHERE id = :idProduct";
    $array = [
        ':visible' => $visible,
        ':idProduct' => $idProduct
    ];
    $result = $conn->execQuery($query, $array);
    return $result;
}

function deleteProduct($idUser, $idProduct){
    // Open Connection with DB
    $conn = new Database();

    // Update Product 
    $query = "DELETE FROM products WHERE idUser = :idUser AND id = :idProduct";
    $array = [
        ':idUser' => $idUser,
        ':idProduct' => $idProduct
    ];
    $result = $conn->execQuery($query, $array);
    return $result;
}

<?php

//MARK: - DEBUGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Read raw POST data
$inputJSON = file_get_contents('php://input');

// Decode JSON data
$inputData = json_decode($inputJSON, true);

//MARK: - INCLUDING HEADERS
require_once "dbConnection.php";
require_once "message.php";

if (isset($inputData['submit'])) {
    $message = new Messages();

    // MARK: - GET FIELD VALUES
    $type = $inputData['type'];
    
    // MARK: - CATEGORY MANAGMENT
    if ($type === 'fetchCategories') {

        // MARK: - FETCH CATEGORIES
        $result = fetchCategories();

        if ($result[0])
        {
            $message->StatusMessage('success', 'Category Manager', 'Category Names retrieved successfully', $result[1]);
        }
        else
            $message->StatusMessage('error', 'Category Manager', 'Error in retrieving User Image');
    }

    //MARK: - PRODUCT CATEGORY (assossiated class) MANAGMENT
    if ($type === 'addProductCategory') {

        // MARK: - GET FIELD VALUES
        $idCategory = $inputData['idCategory'];
        $idProduct = $inputData['idProduct'];

        // MARK: - CHECK IF THER IS ANY ACTIVE IMAGE
        $result = addProductCategory($idCategory, $idProduct);

        if ($result[0]) {
            $message->StatusMessage('success', 'Category Manager', 'Produc Category Add retrieved successfully', $result[1]);
        } else {
            $message->StatusMessage('error', 'Category Manager', $result[1]);
        }
    }

    if($type === 'updateProductCategory'){

        // MARK: - GET FIELD VALUES
        $id = $inputData['idProductCategory'];
        $idCategory = $inputData['idCategory'];
        $idProduct = $inputData['idProduct'];

        // MARK: - CHECK IF THER IS ANY ACTIVE IMAGE
        $result = updateProductCategory($id, $idCategory, $idProduct);

        if ($result[0]) {
            $message->StatusMessage('success', 'Category Manager', 'Product Category Updated Seccussfully', $result[1]);
        } else {
            $message->StatusMessage('error', 'Category Manager', $result[1]);
        }

    }


    if($type === 'deleteProductCategory'){
    }

} else {

    $message = new Messages();
    $message->StatusMessage('error', 'Category Manager', "Page Not Found");

}

//MARK: - PRODUCT CATEGORIES MANAGMENT FUNCTIONS
function addProductCategory($idCategory, $idProduct){

    // Database Connection
    $conn = new Database();

    // MARK: QUERY
    $query = "INSERT INTO productCategories (idProduct, idCategory) VALUES (:idProduct, :idCategory)";
    $array = [
        ':idProduct' => $idProduct,
        ':idCategory' => $idCategory,
    ];
    $result = $conn->execQuery($query, $array);
    
    if($result)
        return [true, 'Category Added Successfully'];
    else
        return [false, 'Error in Adding Category'];
}

function addProductCategories($images, $idProduct = ''){
    // Database Connection
    $conn = new Database();

}

function updateProductCategory($id, $idCategory, $idProduct){

    // Database Connection
    $conn = new Database();

    // MARK: QUERY
    $query = "UPDATE productCategories SET idCategory = :idCategory WHERE idProduct = :idProduct and id = :id";
    $array = [
        ':id' => $id,
        ':idProduct' => $idProduct,
        ':idCategory' => $idCategory,
    ];
    $result = $conn->execQuery($query, $array);
    
    if($result)
        return [true, 'Category Added Successfully'];
    else
        return [false, 'Error in Adding Category'];
}

function updateProductCategories($images, $idProduct = ''){
    
}

function deleteProductCategory($idCategory, $idProduct){

    // Database Connection
    $conn = new Database();

}

//MARK: - CATEGORIES MANAGMENT FUNCTIONS
function fetchCategories(){
    // Database Connection
    $conn = new Database();

    // MARK: - FETCH CATEGORIES QUERY
    $querySelect = "SELECT id, name, path FROM categories";

    // Send Request and Ger the user
    $result = $conn->execQuery($querySelect);

    if ($result) {
        return [true, $result];
    } else {
        return [false, 'Error in Fetching Categories'];
    }
    
}

function addCategory($name, $path){
    // Database Connection
    $conn = new Database();

    // QUERY
    $query = "INSERT INTO categories (name, path) VALUES (:name, :path)";
    $array = [
        ':name' => $name,
        ':path' => $path,
    ];
    $result = $conn->execQuery($query, $array);

    if ($result) {
        return [true, 'Category Added Successfully'];
    } else {
        return [false, 'Error in Adding Category'];
    }
}

function updateCategory($id, $name, $path){
    // Database Connection
    $conn = new Database();

    // QUERY
    $query = "UPDATE categories SET name = :name, path = :path WHERE id = :id";
    $array = [
        ':id' => $id,
        ':name' => $name,
        ':path' => $path,
    ];
    $result = $conn->execQuery($query, $array);

    if ($result) {
        return [true, 'Category Updated Successfully'];
    } else {
        return [false, 'Error in Updating Category'];
    }
}


function deleteImage($id){
    // Database Connection
    $conn = new Database();

    // QUERY
    $query = "DELETE FROM categories WHERE id = :id";
    $array = [
        ':id' => $id,
    ];
    $result = $conn->execQuery($query, $array);

    if ($result) {
        return [true, 'Category Deleted Successfully'];
    } else {
        return [false, 'Error in Deleting Category'];
    }

}


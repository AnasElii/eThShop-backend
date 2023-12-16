<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

//get the current session
session_start();

// Read raw POST data
$inputJSON = file_get_contents('php://input');

// Decode JSON data
$inputData = json_decode($inputJSON, true);

require_once "dbConnection.php";
require_once "message.php";

if(isset($inputData['submit'])){

    $conn = new Database();
    $message = new Messages();

    $type = $inputData['type'];
    $idProduct = $inputData['idProduct'];
    $idUser = $inputData['idUser'];
    $idImage = $inputData['idImage'];

    if($type === 'add'){
        $result = addImage($conn, $idUser, $idProduct);
        
        if($result[0]){
            $message->StatusMessage('success', 'Image Manager', $result[1]);
        } else {
            $message->StatusMessage('error', 'Image Manager', $result[1]);
        }
    }

    if($type === 'delete'){
        $result = deleteImage($conn, $idUser, $idProduct, $idImage);
        
        if($result[0]){
            $message->StatusMessage('success', 'Image Manager', $result[1]);
        } else {
            $message->StatusMessage('error', 'Image Manager', $result[1]);
        }
    }
}

function addImage($conn, $idUser = '', $idProduct = ''){

    // Check if the file was uploaded successfully
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        
        // variable
        $isProduct = false;
        $id = '';

        // Get the file name
        $fileName = $_FILES['image']['name'];

        // Change the user image name to be unique
        $file_parts = pathinfo($fileName);
        $Name = $file_parts['filename'];
        $Extension = $file_parts['extension'];

        // Get the date and time of the uploaded image 
        $timestamp = microtime(true);
        
        // Generate a randome string 
        $unique_string = uniqid('', true);
        
        // Change the file name
        $fileName = $Name . '_' . $timestamp . '_' . $unique_string . '.' . $Extension;

        // Get the file extension
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Check if the file type is allowed
        $allowedFileType = ['jpg', 'jpeg', 'png'];
        if(!in_array($fileExtension, $allowedFileType)){

            return [false, 'File type not allowed'];
        }

        // Check if it's a Profile or Product image
        if($idUser != ''){

            // Move the image to the profile location
            $targeDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/users/' . date('Y') . '/' . date('m');
            $id = $idUser;

        }else{
            
            // Move the file to the desired location
            $targeDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/' . date('Y') . '/' . date('m');
            $id = $idProduct;
            $isProduct = true;

        }

        // Check if the directory not exists
        if(!file_exists($targeDir)){

            // If it doesn't exist, create it recursively
            if(!mkdir($targeDir, 0755, true)){

                // Failed to create the directory 
                return [false, 'Failed to create directory'];

            }
        }

        // Move File to the desire location
        $targetFile = $targeDir . '/' . $fileName;
        move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

        // Getting the Relative Image Path
        // Get the DOCUMENT_ROOT value
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];

        // Use str_replace to remove the DOCUMENT_ROOT portion
        $relativeImagePath = str_replace($documentRoot, '', $targetFile);

        // Message
        $result = addImageToServer($conn, $relativeImagePath, $id, $isProduct);
        return [$result[0], $result[1]];

    }

    // File upload failed
    return [false, 'File upload failed'];
    
}

function addImageToServer($conn, $path, $id, $isProduct)
{
    if(!$isProduct){
        // MARK: - CHECK IF THER IS ANY ACTIVE IMAGE
        $querySelect = "SELECT active FROM userImages WHERE idUser = :idUser AND active = 1";

        // DESACTIVATE ALL THE OLD ACTIVE IMAGES
        $queryUpdate = "UPDATE userImages
                        SET active = FALSE
                        WHERE idUser = :idUser";

        // INSERT NEW USER IMAGE
        $query = "INSERT INTO userImages (path, active, idUser) VALUES (:path, :active, :idUser)";
    
    }
    else
        $query = "INSERT INTO images (path, idProduct) VALUES (:path, :idProduct)";

    $params = [
        ':path' => $path,
    ];
    
    if (!$isProduct) {
        $params[':active'] = 1; // Assuming active defaults to 1 for userImages
        $params[':idUser'] = $id; 
    }else{
        $params[':idProduct'] = $id;
    }

    if(!$isProduct){

        $paramsUpdate = [
            ':idUser' => $id,
        ];

        $resultSelect= $conn->execQuery($querySelect, $paramsUpdate);

        if($resultSelect != null){
            $resultUpdate = $conn->RowCount($queryUpdate, $paramsUpdate);
            if($resultUpdate == null)
                return [false, 'File failed to update DB'];
        }        
    
    }

    $result = $conn->RowCount($query, $params);

    if($result != null){
        return [true, 'File auploaded to DB successfully'];
    }
    
    return [false, 'File failed to uploaed to DB'];
}

function deleteImage($conn, $idUser = '', $idProduct = '', $idImage = '')
{
    // MARK: - VARIABLES
    $isProduct = false; 
    $id = '';

    // MARK: - FUNTIONALITY
    if($idUser != ''){
        
        $query = "SELECT path FROM userImages WHERE idUser = :idUser AND active = :active";
        $params = [
            ':idUser' => $idUser,
            ':active' => 1,
        ];

        $id = $idUser;
    }
    else{

        $query = "SELECT path FROM images WHERE id = :id AND idProduct = :idProduct";
        $params = [
            ':id' => $idImage,
            ':idProduct' => $idProduct,
        ];

        $id = $idProduct;
        $isProduct = true;

    }
    $result = $conn->execQuery($query, $params);

    // MARK: - CHECK IF THE PATH EXIST
    if($result == null)
        return [false, 'Failed to get path'];

    
    // MARK: - GET IMAGE FULL PATH
    $imagePath = $result[0]['path'];
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
    echo 

    // MARK: - DELETE IMAGE FROM DB
    $data = deleteImageFromServer($conn, $imagePath, $id, $isProduct);

    // MARK: - CHECK IF THE DELETE FROM DB OPERATION WORK FINE
    if($data[0] == null)
        return [$data[0], $data[1]];

    // MARK: - DELETE IMAGE FROM SERVER
    if(file_exists($fullPath))
      if(unlink($fullPath))
        return [true, 'File deleted from SERVER And DB successfully'];
      else 
        return [false, 'File failed to delete from SERVER'];
    else
      return [false, 'File not exist on the SERVER'];
}

function deleteImageFromServer($conn, $path, $id = '', $isProduct){
    
    if(!$isProduct){
        // MARK: - SELECT UserImages ID's
        $querySelect = "SELECT id FROM userImages WHERE idUser = :id ORDER BY id DESC LIMIT 1";

        // MARK: - DELETE userImage
        $query = "DELETE FROM userImages WHERE idUser = :id AND path = :path";

         // MARK: - UPDATE THE 'active' FLAG FOR THE PREVIEW RECORD
         $queryUpdate = "UPDATE userImages SET active = TRUE WHERE idUser = :idUser AND id = :latestId";
    } else{
        $query = "DELETE FROM images WHERE idProduct = :id AND path = :path";

    }
    
    $params = [
        ':id' => $id,
        ':path' => $path,
    ];

    // MARK: DELETE SELECTED IMAGE
    $result = $conn->RowCount($query, $params);

    if(!$isProduct){

        // MARK: - GET THE LATEST RECORD
        $selectParams = [
            ':id' => $id,
        ];
        $selectResult = $conn->Query($querySelect, $selectParams);
        $latestId = $selectResult->fetchColumn();
        echo "Id Fetched ID: ".$latestId;

        // MARK: UPDATE THE 'active' FLAG FOR THE LATEST ROW
        if ($latestId) {
            $selectParams = [
                ':idUser' => $id,
                ':latestId' => $latestId
            ];
            $resultUpdate = $conn->rowCount($queryUpdate, $selectParams);
        }

    }

    if($result != null){
        return [true, 'File deleted from DB successfully'];
    }
    
    return [false, 'File failed to delete from DB'];

}

<?php

// DEBUGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

// INCLUDING HEADERS
require_once "dbConnection.php";
require_once "message.php";

if (isset($_POST['submit'])) {

    $conn = new Database();
    $message = new Messages();

    $type = $_POST['type'];
    

    if ($type === 'fetch') {
        // MARK: - GET USER ID
        $idUser = $_POST['idUser'];

        // MARK: - CHECK IF THER IS ANY ACTIVE IMAGE
        $querySelect = "SELECT path FROM userImages WHERE idUser = :idUser AND active = 1";
        $array = [];
        array_push($array, $idUser);

        // Send Request and Ger the user
        $userImage = $conn->execQuery($querySelect, $array);

        if ($userImage)
            $message->StatusMessage('success', 'Image Manager', 'User Data retrieved successfully', $userImage);
        else
            $message->StatusMessage('error', 'Image Manager', 'Error in retrieving User Image');

    }

    if ($type === 'addImage') {
        // MARK: - GET FIELD VALUES
        $idUser = $_POST['idUser'];
        $idProduct = $_POST['idProduct'];
        $imageFile = $_FILES['image'];

        // MARK: - CHECK IF THER IS ANY ACTIVE IMAGE
        $result = addImage($conn, $imageFile, $idUser, $idProduct);

        if ($result[0]) {
            $message->StatusMessage('success', 'Image Manager', $result[1], $result[2]);
        } else {
            $message->StatusMessage('error', 'Image Manager', $result[1]);
        }
    }

    if ($type === 'addImages') {
        // MARK: - GET FIELD VALUES
        $idProduct = $_POST['idProduct'];
        $listLength = $_POST['listLength'];

        // MARK: - CHECK IF THER IS ANY ACTIVE IMAGE
        $images = array();
        for ($i = 1; $i <= $listLength; $i++)
            array_push($images, $_FILES['image' . $i]);

        // MARK: - CHECK IF THER IS ANY ACTIVE IMAGE
        $result = addImages($images, $idProduct);
        
        if($result[0]){
            $message->StatusMessage('success', 'Image Manager', 'Image upload successfully', $result);
        } else {
            $message->StatusMessage('error', 'Image Manager', 'Error upload image', $result);
        }
    }

    if($type === 'updateImages'){
            // MARK: - GET FIELD VALUES
            $idProduct = $_POST['idProduct'];
            $listLength = $_POST['listLength'];

            // MARK: - CHECK IF THER IS ANY ACTIVE IMAGE
            $images = array();
            for ($i = 1; $i <= $listLength; $i++)
                array_push($images, $_FILES['image' . $i]);

            // MARK: - DELETE ALL IMAGES FROM IMAGE LIST
            $result = deleteImagesFromDB($conn, $idProduct);

            if($result[0]){
                                
                // MARK: - CHECK IF THER IS ANY ACTIVE IMAGE
                $result = addImages($images, $idProduct);
                            
                if($result[0][0]){
                    $message->StatusMessage('success', 'Image Manager', $result[0][1]);
                } else {
                    $message->StatusMessage('error', 'Image Manager',  $result[0][1]);
                }

            }else{
                $message->StatusMessage('error', 'Image Manager', $result[1]);
            }
            
    }

    // if($type === 'deleteImage'){
    //     $idImage = $_POST['idImage'];
    //     $result = deleteImage($conn, $idUser, $idProduct, $idImage);

    //     if($result[0]){
    //         $message->StatusMessage('success', 'Image Manager', $result[1]);
    //     } else {
    //         $message->StatusMessage('error', 'Image Manager', $result[1]);
    //     }
    // }

    if($type === 'deleteImages'){

    }
} else {
    $message = new Messages();
    $message->StatusMessage('error', 'Image Manager', "Page Not Found");
}

// Add Managment
function addImage($conn, $imageFile, $idUser = '', $idProduct = ''){

    // Check if the file was uploaded successfully
    if (isset($_FILES['image']) && $imageFile['error'] == 0) {

        // variable
        $isProduct = false;
        $id = '';

        // Get the file name
        $fileName = $imageFile['name'];

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
        if (!in_array($fileExtension, $allowedFileType)) {
            return [false, 'File type not allowed!'];
        }

        // Check if it's a Profile or Product image
        if ($idUser != '') {

            // Move the image to the profile location
            $targeDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/users/' . date('Y') . '/' . date('m');
            $id = $idUser;

        } else {

            // Move the file to the desired location
            $targeDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/' . date('Y') . '/' . date('m');
            $id = $idProduct;
            $isProduct = true;

        }

        // Check if the directory not exists
        if (!file_exists($targeDir)) {

            // If it doesn't exist, create it recursively
            if (!mkdir($targeDir, 0755, true)) {

                // Failed to create the directory 
                return [false, 'Failed to create directory'];

            }
        }

        // Move File to the desire location
        $targetFile = $targeDir . '/' . $fileName;
        move_uploaded_file($imageFile['tmp_name'], $targetFile);

        // Getting the Relative Image Path
        //-> Get the DOCUMENT_ROOT value
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];

        //-> Use str_replace to remove the DOCUMENT_ROOT portion
        $relativeImagePath = str_replace($documentRoot, '', $targetFile);

        // Message
        return addImageToDB($conn, $relativeImagePath, $id, $isProduct);
        // return [$result[0], $result[1]];

    }

    // File upload failed
    return [false, 'File upload failed'];

}

function addImages($images, $idProduct = ''){
    // Database Connection
    $conn = new Database();

    // Result List
    $resultList = array();
    
    // Check if the file was uploaded successfully
    if (count($images) > 0) {

        foreach ($images as $imageFile) {
            // variable

            // Get the file name
            $fileName = $imageFile['name'];

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
            if (!in_array($fileExtension, $allowedFileType)) {
                return [false, 'File type not allowed!'];
            }

            // Move the file to the desired location
            $targeDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/' . date('Y') . '/' . date('m');

            // Check if the directory not exists
            if (!file_exists($targeDir)) {

                // If it doesn't exist, create it recursively
                if (!mkdir($targeDir, 0755, true)) {

                    // Failed to create the directory 
                    return [false, 'Failed to create directory'];
                    
                }
            }

            // Move File to the desire location
            $targetFile = $targeDir . '/' . $fileName;
            move_uploaded_file($imageFile['tmp_name'], $targetFile);

            // Getting the Relative Image Path
            //-> Get the DOCUMENT_ROOT value
            $documentRoot = $_SERVER['DOCUMENT_ROOT'];

            //-> Use str_replace to remove the DOCUMENT_ROOT portion
            $relativeImagePath = str_replace($documentRoot, '', $targetFile);

            // Message
            $result = addImageToDB($conn, $relativeImagePath, $idProduct, true);
            array_push($resultList, [$result[0], $result[1]]);
        }

        // Return result
        return $resultList;

    }

    // File upload failed
    return [false, 'File upload failed'];

}

function addImageToDB($conn, $path, $id, $isProduct){
    if (!$isProduct) {
        // MARK: - CHECK IF THER IS ANY ACTIVE IMAGE
        $querySelect = "SELECT active FROM userImages WHERE idUser = :idUser AND active = 1";

        // DESACTIVATE ALL THE OLD ACTIVE IMAGES
        $queryUpdate = "UPDATE userImages
                        SET active = FALSE
                        WHERE idUser = :idUser";

        // INSERT NEW USER IMAGE
        $query = "INSERT INTO userImages (path, active, idUser) VALUES (:path, :active, :idUser)";

    } else
        $query = "INSERT INTO images (path, idProduct) VALUES (:path, :idProduct)";

    $params = [
        ':path' => $path,
    ];

    if (!$isProduct) {
        $params[':active'] = 1; // Assuming active defaults to 1 for userImages
        $params[':idUser'] = $id;
    } else {
        $params[':idProduct'] = $id;
    }

    if (!$isProduct) {

        $paramsUpdate = [
            ':idUser' => $id,
        ];

        $resultSelect = $conn->execQuery($querySelect, $paramsUpdate);

        if ($resultSelect != null) {
            $resultUpdate = $conn->RowCount($queryUpdate, $paramsUpdate);
            if ($resultUpdate == null)
                return [false, 'Image failed to update DB'];
        }

    }

    $result = $conn->RowCount($query, $params);

    if ($result != null) {
        if(!$isProduct)
            return [true, 'Image auploaded to DB successfully', $path];
        else
            return [true, 'Image auploaded to DB successfully'];
    }

    return [false, 'Image failed to uploaed to DB'];
}

// Delete Managment
function deleteImage(){

}

function deleteImages(){

}

function deleteImagesFromDB($conn, $idProduct){
    $query = "DELETE FROM images WHERE idProduct = :idProduct";
    $array = [
        ':idProduct' => $idProduct,
    ];

    $result = $conn->execQuery($query, $array);
    
    if($result != null){
        return [true, 'Image Deleted From DB'];
    }
    
    return [false, 'Error Deleting Image From DB'];
}
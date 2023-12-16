<?php

require_once 'dbConnection.php';
require_once 'message.php';

if (isset($_POST["submit"])) {

    $type = $_POST["type"];
    $conn = new Database();
    $message = new Messages();

    if ($type == "all") {

        $idUser = $_POST["idUser"];
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
            i.path,
            c.name AS category
        From products p
        INNER JOIN images i ON p.id = i.idProduct
        INNER JOIN productCategories pc ON p.id = pc.idProduct
        INNER JOIN categories c ON pc.idCategory = c.id
        WHERE p.visible = true
        AND i.id IN (SELECT MIN(id) FROM images GROUP BY idProduct)
        AND pc.id IN (SELECT MIN(id) FROM productCategories GROUP BY idProduct)
        ORDER BY p.id DESC";
        $data = $conn->execQuery($query);

        // Fetch Liked Products From DB
        $likedListQuery = "SELECT
                         * 
                        FROM likes
                        WHERE idUser = :idUser";
        $array = [];
        array_push($array, $idUser);
        $likedList = $conn->execQuery($likedListQuery, $array);

        // Fetch Favorite Products From DB
        $query = "SELECT * FROM favorites WHERE idUser = :idUser";
        $array = [];
        array_push($array, $idUser);
        $favoriteList = $conn->execQuery($query, $array);

        // Fetch User Products From DB
        $query = "SELECT id FROM products WHERE idUser = :idUser";
        $array = [];
        array_push($array, $idUser);
        $userList = $conn->execQuery($query, $array);

        echo json_encode([
            'status' => 'success',
            'type' => 'fetchProducts',
            'message' => 'Products fetched successfully',
            'likedList' => $likedList,
            'favoriteList' => $favoriteList,
            'userList' => $userList,
            'data' => $data,
            'idUser' => $idUser
        ]);

    }

    if ($type == "product") {

        // Get POST Data
        $idProduct = $_POST["idProduct"];
        $idUser = $_POST["idUser"];

        // Get Product Data
        $query = "SELECT p.*, 
                        i.path, 
                        u.username, 
                        u.tel, 
                        u.valid
                From products p 
                INNER JOIN images i ON p.id = i.idProduct
                INNER JOIN users u ON p.idUser = u.id
                WHERE p.id = :idProduct";
        $array = [];
        array_push($array, $idProduct);
        $data = $conn->execQuery($query, $array);

        // Check If The Product Liked
        $query = "SELECT * FROM likes WHERE idUser = :idUser AND idProduct = :idProduct";
        $array = [];
        array_push($array, $idUser);
        array_push($array, $idProduct);
        $isLiked = $conn->execQuery($query, $array);
        $isLiked == null ? $data[0]['isLiked'] = false : $data[0]['isLiked'] = true;

        // Get Seller Profile Image

        // Send Data As a Json file
        echo json_encode([
            'status' => 'success',
            'type' => 'fetchProducts',
            'message' => 'Products fetched successfully',
            // 'isLiked' => $isLiked,
            'data' => $data,
            'isLiked' => $isLiked,
            'userID' => $idUser
        ]);

    }

    if ($type == 'productForUpdate') {
        $idProduct = $_POST['idProduct'];
        $idUser = $_POST['idUser'];

        $result = fetchUPdateProductInfo($conn, $idProduct, $idUser);

        if ($result) {
            
            // FETCH PRODUCT CATEGORY & IMAGES
            $category = fetchProdutCategory($conn, $idProduct);
            $images = fetchProductImages($conn, $idProduct);
            
            // ADD CATEGORY & IMAGES TO RESULT
            $result[0]['category'] = $category;
            $result[0]['images'] = $images;

            $message->StatusMessage('success', 'Product Manager', 'Product fetched successfully', $result);
        } else
            $message->StatusMessage('error', 'Product Manager', 'Error fetching product');

    }

    if ($type == 'userProductsCount') {
        $idUser = $_POST['idUser'];
        $query = "SELECT COUNT(*) FROM products WHERE idUser = :idUser";
        $array = [];
        array_push($array, $idUser);
        $result = $conn->execQuery($query, $array);
        if ($result) {

            $new_key = array('count');

            foreach ($result as $index => $record) {
                $result[$index] = array_combine($new_key, $record);
            }

            $message->StatusMessage('success', 'Product Manager', 'Product count fetched successfully', $result);
        } else
            $message->StatusMessage('error', 'Product Manager', 'Error fetching product count');
    }

    if ($type == "userProducts") {

        // Get POST Data
        $idUser = $_POST["idUser"];

        //Querys
        // Fetch User Products
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
            From products p 
            INNER JOIN images i ON p.id = i.idProduct 
            WHERE p.idUser = :idUser
            AND visible = true
            AND i.id IN (SELECT MIN(id) FROM images GROUP BY idProduct)
            ORDER BY p.id DESC";

        $data = fetchData($query, $idUser, "");

        // Fetch Liked Products From DB
        $query = "SELECT
                    * 
                FROM likes
                WHERE idUser = :idUser";

        $likedList = fetchData($query, $idUser, "");


        // Fetch Favorite Products From DB
        $query = "SELECT 
                    * 
                FROM favorites 
                WHERE idUser = :idUser";

        $favoriteList = fetchData($query, $idUser, "");

        echo json_encode([
            'status' => 'success',
            'type' => 'fetchProducts',
            'message' => 'Products fetched successfully',
            'likedList' => $likedList,
            'favoriteList' => $favoriteList,
            'data' => $data,
            'idUser' => $idUser
        ]);
    }

    if ($type == "userHiddenProducts") {

        // Get POST Data
        $idUser = $_POST["idUser"];

        //Querys
        // Fetch User Products
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
                From products p 
                INNER JOIN images i ON p.id = i.idProduct 
                WHERE p.idUser = :idUser 
                AND p.visible = false
                AND i.id IN (SELECT MIN(id) FROM images GROUP BY idProduct)
                ORDER BY p.id DESC";

        $data = fetchData($query, $idUser, "");

        // Fetch Liked Products From DB
        $query = "SELECT
                    * 
                FROM likes
                WHERE idUser = :idUser";

        $likedList = fetchData($query, $idUser, "");


        // Fetch Favorite Products From DB
        $query = "SELECT 
                    * 
                FROM favorites 
                WHERE idUser = :idUser";

        $favoriteList = fetchData($query, $idUser, "");

        echo json_encode([
            'status' => 'success',
            'type' => 'fetchProducts',
            'message' => 'Products fetched successfully',
            'likedList' => $likedList,
            'favoriteList' => $favoriteList,
            'data' => $data,
            'idUser' => $idUser
        ]);
    }

} else {
    echo json_encode([
        'status' => 'error',
        'type' => 'fetchProducts',
        'message' => 'Error fetching products'
    ]);
}

function fetchData($query, $idUser, $idProduct = "")
{
    // Open Connection
    $conn = new Database();

    // get Data From Server
    $array = [];
    array_push($array, $idUser);

    if ($idProduct !== "")
        array_push($array, $idProduct);

    // return result
    return $conn->execQuery($query, $array);
}

function fetchUPdateProductInfo($conn, $idProduct, $idUser)
{
    $query = "SELECT * FROM products WHERE id = :idProduct AND idUser = :idUser";
    $array = [
        ':idProduct' => $idProduct,
        ':idUser' => $idUser
    ];
    $result = $conn->execQuery($query, $array);
    return $result;
}

function fetchProductImages($conn, $idProduct)
{
    // FETCH PRODUCT IMAGES
    $query = "SELECT * FROM images WHERE idProduct = :idProduct";
    $array = [
        ':idProduct' => $idProduct
    ];
    $images = $conn->execQuery($query, $array);
    return $images;
}

function fetchProdutCategory($conn, $idProduct){
    // FETCH PRODUCT CATEGORY
    $query = "SELECT * FROM productCategories WHERE idProduct = :idProduct LIMIT 1";
    $array = [
        ':idProduct' => $idProduct
    ];
    $category = $conn->execQuery($query, $array);
    return $category;
}
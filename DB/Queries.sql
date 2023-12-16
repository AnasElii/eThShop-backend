-- Active: 1681660018829@@127.0.0.1@3306

-- Query: Fetch all products for listing

SELECT
    id,
    name,
    price,
    sold,
    location,
    dateAdd,
    bestseller
From products;

-- Query: Fetch all products for listing inner joing with images

SELECT
    p.id,
    p.name,
    p.price,
    p.sold,
    p.location,
    p.dateAdd,
    p.bestseller,
    i.path
From products p
    INNER JOIN images i ON p.id = i.idProduct;


-- Query: Fetch all products for listing inner joing with images and the Seller info

SELECT
    p.id,
    p.name,
    p.price,
    p.sold,
    p.location,
    p.dateAdd,
    p.bestseller,
    p.idUser,
    i.path,
    u.username,
    u.tel,
    u.valid
From products p
    INNER JOIN images i ON p.id = i.idProduct
    INNER JOIN users u ON p.id = u.id;

-- Query: Fetch Is Product Liked 

SELECT
    p.id,
    p.name,
    p.price,
    p.sold,
    p.location,
    p.dateAdd,
    p.bestseller,
    p.idUser,
    u.id,
    u.username,
    l.id,
    i.path
From products p
    INNER JOIN images i ON p.id = i.idProduct
    INNER JOIN users u ON p.idUser = u.id
    INNER JOIN likes l ON p.id = l.idProduct AND u.id = l.idUser
    WHERE p.id = 3;
    
-- Query: Fetch Is Product Liked 

SELECT 
    *
    FROM likes
    WHERE idUser = 2 AND  idProduct = 3;

INSERT INTO likes(idUser, idProduct) VALUES(2, 3);


-- Query: Fetch Favorites 

SELECT
    *
From favorites f
    INNER JOIN users u ON f.idUser = u.id
    INNER JOIN products p ON f.idProduct = p.id
    WHERE f.idUser = 3;
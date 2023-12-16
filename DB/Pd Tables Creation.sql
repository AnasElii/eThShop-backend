
CREATE DATABASE DEFAULT CHARACTER SET = utf8mb4;

ALTER TABLE product DROP FOREIGN KEY idSeller;

-- Create Users Table and Users Images Table
CREATE TABLE
    users(
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(255) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(255) NOT NULL
    );

CREATE TABLE 
    userImages(
        id INT PRIMARY KEY AUTO_INCREMENT,
        path varchar(255) NOT NULL,
        idUser int NOT NULL,
        FOREIGN KEY (idUser) REFERENCES users(id) ON DELETE CASCADE
    );

-- Trigger 
DELIMITER $$
CREATE TRIGGER after_image_insert
AFTER INSERT ON userImages
FOR EACH ROW
BEGIN
    -- Deactivate all other images for the same user
    UPDATE userImages
    SET active = FALSE
    WHERE user_id = NEW.user_id AND image_id != NEW.image_id;
    
    -- Activate the newly inserted image
    UPDATE userImages
    SET active = TRUE
    WHERE image_id = NEW.image_id;
END;
$$
DELIMITER ;

-- Create Product Table

CREATE TABLE
    products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description varchar(255) NOT NULL,
        price decimal(10, 2) NOT NULL,
        sold INT DEFAULT 0,
        location varchar(255) NOT NULL,
        dateAdd datetime NOT NULL,
        state BOOLEAN NOT NULL DEFAULT TRUE,
        idUser int NOT NULL,
        FOREIGN KEY (idUser) REFERENCES users(id) ON DELETE CASCADE
    );

-- Add Visible Column In products Table

ALTER TABLE products 
ADD visible BOOLEAN NOT NULL DEFAULT TRUE;

-- Create Images Table << Images of the product >>

CREATE TABLE
    images (
        id int PRIMARY KEY AUTO_INCREMENT,
        path varchar(255) NOT NULL,
        idProduct int NOT NULL,
        FOREIGN KEY (idProduct) REFERENCES products(id) ON DELETE CASCADE
    );

CREATE TABLE
    categories (
        id int PRIMARY KEY AUTO_INCREMENT,
        name varchar(255) UNIQUE NOT NULL
    );

-- CREATE TABLE
--     productCategories (
--         id int PRIMARY KEY AUTO_INCREMENT,
--         idProduct int NOT NULL,
--         idCategories int NOT NULL,
--         UNIQUE KEY (idProduct, idCategories),
--         FOREIGN KEY (idProduct) REFERENCES products(id),
--         FOREIGN KEY (idCategories) REFERENCES categories(id)
--     );

CREATE TABLE `productCategories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idProduct` int NOT NULL,
  `idCategory` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idProduct` (`idProduct`,`idCategory`),
  KEY `idCategories` (`idCategory`),
  CONSTRAINT `productCategories_ibfk_1` FOREIGN KEY (`idProduct`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `productCategories_ibfk_2` FOREIGN KEY (`idCategory`) REFERENCES `categories` (`id`)
);

-- Create Followers Table
-- CREATE TABLE 
--     followers(
--         id INT PRIMARY KEY AUTO_INCREMENT,
--         idFollower int NOT NULL,
--         idFollowed int NOT NULL,
--         UNIQUE KEY(idFollower),
--         FOREIGN KEY (idFollower) REFERENCES users(id) ON DELETE CASCADE
--     );

CREATE TABLE `followers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idFollower` int NOT NULL,
  `idFollowed` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idFollower_2` (`idFollower`,`idFollowed`),
  CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`idFollower`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `no_self_following` CHECK ((`idFollower` <> `idFollowed`))
)

-- Create Favorites Table
CREATE TABLE `favorites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idUser` int NOT NULL,
  `idProduct` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idUser` (`idUser`,`idProduct`),
  KEY `favorites_ibfk_2` (`idProduct`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`),
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`idProduct`) REFERENCES `products` (`id`) ON DELETE CASCADE
)

-- Create Likes Table
CREATE TABLE `likes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idUser` int NOT NULL,
  `idProduct` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idUser` (`idUser`,`idProduct`),
  KEY `likes_ibfk_2` (`idProduct`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`),
  CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`idProduct`) REFERENCES `products` (`id`) ON DELETE CASCADE
)




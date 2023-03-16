
CREATE DATABASE 'db_management';

CREATE TABLE users (
    id TINYINT UNSIGNED AUTO_INCREMENT NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by  TINYINT UNSIGNED NULL,
    updated_date DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    updated_by TINYINT UNSIGNED NULL,
    deleted_date DATETIME DEFAULT NULL,
    deleted_by TINYINT UNSIGNED NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (deleted_by) REFERENCES users(id)
);

CREATE TABLE distances(
    id INT UNSIGNED AUTO_INCREMENT NOT NULL,
    car_license_plate CHAR(7) NOT NULL,
    car_rental_month DATE NOT NULL,
    km  MEDIUMINT UNSIGNED NOT NULL,
    email VARCHAR(50) NOT NULL,
    validation ENUM('not_valid', 'valid') DEFAULT 'not_valid',
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by TINYINT UNSIGNED NULL,
    updated_date DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    updated_by TINYINT UNSIGNED NULL ,
    deleted_date DATETIME,
    deleted_by TINYINT UNSIGNED NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (deleted_by) REFERENCES users(id),
    INDEX (car_license_plate),
    INDEX (car_rental_month),
    INDEX (validation)
);




-- 1. Initialize Database
CREATE DATABASE IF NOT EXISTS order_achievements CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE order_achievements;

-- 2. Create Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Create Achievements Table
-- category_id uses RESTRICT: You cannot delete a category if it has achievements.
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    points INT NOT NULL DEFAULT 0,
    image_url VARCHAR(2048),
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_achievement_category
        FOREIGN KEY (category_id) 
        REFERENCES categories(id) 
        ON DELETE RESTRICT -- Prevents category deletion if achievements exist
) ENGINE=InnoDB;

-- 4. Create Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Create Many-to-Many Relation Table
-- achievement_id and user_id use RESTRICT: 
-- You cannot delete an achievement if a user owns it.
-- You cannot delete a user if they still have achievements assigned.
CREATE TABLE user_achievements (
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id, achievement_id), -- Composite PK as required

    CONSTRAINT fk_ua_user
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE RESTRICT, -- Prevents user deletion if they have achievements

    CONSTRAINT fk_ua_achievement
        FOREIGN KEY (achievement_id) 
        REFERENCES achievements(id) 
        ON DELETE RESTRICT -- Prevents achievement deletion if assigned to users
) ENGINE=InnoDB;
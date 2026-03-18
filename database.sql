-- ToDoList – MySQL 8 schema
-- Run once to prepare the database before starting the application.

CREATE DATABASE IF NOT EXISTS todolist
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE todolist;

CREATE TABLE IF NOT EXISTS tasks (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    title      VARCHAR(255)    NOT NULL,
    completed  TINYINT(1)      NOT NULL DEFAULT 0,
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

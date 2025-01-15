<?php
$client = new mysqli(getenv("DB_SERVER"), getenv("DB_USER"), getenv("DB_PASSWORD"));
$client->query("CREATE DATABASE IF NOT EXISTS `pud`");
$client->select_db("pud");
$client->query("create table if not exists users (Id int auto_increment primary key, Username varchar(64) not null, Password varchar(64) not null, `Rank` int null);");
$client->query("CREATE TABLE IF NOT EXISTS collections (Id INT AUTO_INCREMENT PRIMARY KEY, Name VARCHAR(64) NOT NULL, UID VARCHAR(64) NOT NULL, Description TEXT, Author VARCHAR(96), CONSTRAINT Name UNIQUE (Name), CONSTRAINT UID UNIQUE (UID))");
$client->query("CREATE TABLE IF NOT EXISTS plugins (Id INT AUTO_INCREMENT PRIMARY KEY, Name VARCHAR(255) NOT NULL, Version VARCHAR(50) NOT NULL, Path VARCHAR(255) NOT NULL, UID_Id INT NOT NULL, Changelog TEXT, Hash VARCHAR(128) NOT NULL)");
$password = readline("Please specify the password you would like to use for 'Admin': ");
$pw = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
$client->query("INSERT INTO pud.users (Username, Password, `Rank`) VALUES ('Admin', '$pw', 8)");
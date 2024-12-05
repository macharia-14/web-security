DROP DATABASE IF exists arthive;
create database arthive;
USE arthive;
CREATE TABLE Artist (
  username VARCHAR(50) PRIMARY KEY NOT NULL,
  artist_id INT NOT NULL,
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  email VARCHAR(100),
  phone_number VARCHAR(20),
  password VARCHAR(100)  
);
CREATE TABLE Art_submission (
  artist_id INT PRIMARY KEY NOT NULL,
  username VARCHAR(50),
  art_name VARCHAR(50),
  price DECIMAL(10,2),
  email VARCHAR(100),
  contact VARCHAR(20),
  art VARCHAR(20)  
);
CREATE TABLE Exhibition (
  event_id INT PRIMARY KEY,
  art_name VARCHAR(50),
  username VARCHAR(50),
  name VARCHAR(100),
  location VARCHAR(100),
  time DATETIME
);
CREATE TABLE Gallery (
  art_id INT PRIMARY KEY,
  artist_id INT,
  art_name VARCHAR(50),
  price DECIMAL(10,2),
  email VARCHAR(100),
  contact VARCHAR(20)  
);

alter table Artist
add FOREIGN KEY (artist_id) REFERENCES Art_submission(artist_id);

alter table Art_submission
add FOREIGN KEY (username) REFERENCES Artist(username);

alter table Gallery
add FOREIGN KEY (artist_id) REFERENCES Artist(artist_id)
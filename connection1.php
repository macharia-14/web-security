<?php

//making a database connection
$serverName = getenv('DB_SERVER') ?: "127.0.0.1";


//connection object 
$conn = new mysqli($serverName, $userName, $password, $dbName);

//check for errors and connect to the database
if ($conn->connect_error) {
    error_log('Database connection failed:' . $conn->connect_error);
    die('Database connection error. Please try again later.');
}
//Optional: Force the connection to use UTF-8
$conn->set_charset('utf8mb4');

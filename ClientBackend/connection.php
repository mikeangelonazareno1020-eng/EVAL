<?php
// File: connection.php
// Path: /ClientBackend/connection.php

function conn()
{
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'hcc-multicampus';

    $con = mysqli_connect($host, $username, $password, $database);

    if (!$con) {
        die('Database connection failed: ' . mysqli_connect_error());
    }

    return $con;
}

?>
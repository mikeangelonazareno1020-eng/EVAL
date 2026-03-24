<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hcc-multicampus";

// $servername = "localhost";
// $username = "u469776567_multicampus";
// $password = "Hcc_multicampus1946";
// $dbname = "u469776567_multicampus";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
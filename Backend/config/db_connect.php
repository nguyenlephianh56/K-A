<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kvaa"; 

// Tạo kết nối và gán vào biến $conn
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

?>
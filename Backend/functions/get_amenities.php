<?php
require_once '../config/db_connect.php';

$sql = "SELECT amenity_id, name FROM amenities ORDER BY name";
$result = $conn->query($sql);

$amenities = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $amenities[] = $row;
    }
}
?>
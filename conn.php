<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "avianna_resort"; // <-- MAKE SURE THIS HAS NO 's' AT THE END

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
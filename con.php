<?php
// Include your database connection code here (e.g., db_conn.php)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scanandpay"; // Replace with your actual database name

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize success and error variables
$registrationSuccess = false;
$registrationError = "";
?>
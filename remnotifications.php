<?php
// Include the database connection configuration
require("con.php");

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // SQL query to delete all notifications
        $sql = "DELETE FROM notifications";

        // Execute the query
        $result = $conn->query($sql);

        if ($result) {
            $response['status'] = true;
            $response['message'] = "All notifications removed successfully";
        } else {
            $response['status'] = false;
            $response['message'] = "Failed to remove notifications: " . $conn->error;
        }
    } else {
        $response['status'] = false;
        $response['message'] = "Invalid request method. Only POST requests are allowed.";
    }
} catch (Exception $e) {
    // Handle any exceptions
    $response['status'] = false;
    $response['message'] = "Error: " . $e->getMessage();
}

// Convert the response array to JSON and echo it
header('Content-Type: application/json');
echo json_encode($response);
?>

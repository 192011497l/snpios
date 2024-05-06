<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Check if the 'id' parameter is set in the request
        if (isset($_POST['username'])) {
            // Get the user ID from the request
            $userId = $_POST['username'];

            // SQL query to fetch user details based on ID using a prepared statement
            $sql = "SELECT username, COALESCE(email, '') AS email, COALESCE(mobile_no, '') AS mobile_no, COALESCE(shop_name, '') AS shop_name FROM signup WHERE username = ?";
            
            // Prepare the statement
            $stmt = $conn->prepare($sql);

            // Bind the parameter
            $stmt->bind_param("i", $userId);

            // Execute the query
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            if ($result) {
                // Fetch the row as an associative array
                $data = $result->fetch_assoc();

                if ($data) {
                    $response['status'] = true;
                    $response['message'] = "Data showing successfully";
                    $response['data'] = array($data);
                } else {
                    $response['status'] = false;
                    $response['message'] = "User with ID $userId not found.";
                    $response['data'] = [];
                }
            } else {
                $response['status'] = false;
                $response['message'] = "Query execution failed: " . $stmt->error;
                $response['data'] = [];
            }

            // Close the statement
            $stmt->close();
        } else {
            $response['status'] = false;
            $response['message'] = "User ID not provided.";
            $response['data'] = [];
        }
    } else {
        $response['status'] = false;
        $response['message'] = "Invalid request method. Only POST requests are allowed.";
        $response['data'] = [];
    }
} catch (Exception $e) {
    // Handle any exceptions
    $response['status'] = false;
    $response['message'] = "Error: " . $e->getMessage();
    $response['data'] = [];
}

// Convert the response array to JSON and echo it
header('Content-Type: application/json');
echo json_encode($response);
?>

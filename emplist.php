<?php
// Include the database connection configuration
require("con.php");

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        // SQL query to fetch patient information
        $sql = "SELECT id, name, age, role, mobile_no, status FROM employeemanagement";

        // Execute the query
        $result = $conn->query($sql);

        if ($result) {
            $data = array();

            // Fetch all rows as an associative array
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            if (count($data) > 0) {
                $response['status'] = true;
                $response['message'] = "Data showing successfully";
                $response['data'] = $data;
            } else {
                $response['status'] = false;
                $response['message'] = "0 results";
                $response['data'] = [];
            }
        } else {
            $response['status'] = false;
            $response['message'] = "Query execution failed: " . $conn->error;
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

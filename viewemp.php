<?php
// Include the database connection configuration
require("con.php");

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Check if the 'id' parameter is set in the request
        if (isset($_POST['id'])) {
            // Get the employee ID from the request
            $employeeId = $_POST['id'];

            // SQL query to fetch employee details based on ID using a prepared statement
            $sql = "SELECT name, email, mobile_no, age, qualification, experience, role, address FROM employeemanagement WHERE id = ?";
            
            // Prepare the statement
            $stmt = $conn->prepare($sql);

            // Bind the parameter
            $stmt->bind_param("i", $employeeId);

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
                    $response['message'] = "Employee with ID $employeeId not found.";
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
            $response['message'] = "Employee ID not provided.";
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

<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the 'bill_id' parameter is set in the request
        if (isset($_POST['bill_id'])) {
            // Get the bill ID from the request
            $bill_id = $_POST['bill_id'];

            // SQL query to fetch data for a specific bill
            $select_query = "SELECT bill_id, bill_date, bill_value, bill_status FROM bill WHERE bill_id = ?";
            $stmt = $conn->prepare($select_query);

            // Bind the parameter
            $stmt->bind_param("i", $bill_id);

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
                    $response['message'] = "Bill with ID $bill_id not found.";
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
            $response['message'] = "Bill ID not provided.";
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

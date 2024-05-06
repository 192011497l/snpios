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
            // Get the offer ID from the request
            $offerId = $_POST['id'];

            // SQL query to fetch offer details based on ID using a prepared statement
            $sql = "SELECT  offer_name, expiry_date, offer_details FROM offers WHERE id = ?";

            // Prepare the statement
            $stmt = $conn->prepare($sql);

            // Check if the statement was prepared successfully
            if ($stmt) {
                // Bind the parameter
                $stmt->bind_param("i", $offerId);

                // Execute the query
                if ($stmt->execute()) {
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
                            $response['message'] = "Offer with ID $offerId not found.";
                            $response['data'] = [];
                        }
                    } else {
                        $response['status'] = false;
                        $response['message'] = "Query execution failed: " . $stmt->error;
                        $response['data'] = [];
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = "Error executing statement: " . $stmt->error;
                    $response['data'] = [];
                }

                // Close the statement
                $stmt->close();
            } else {
                $response['status'] = false;
                $response['message'] = "Error preparing statement: " . $conn->error;
                $response['data'] = [];
            }
        } else {
            $response['status'] = false;
            $response['message'] = "Offer ID not provided.";
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

<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Retrieve all payments data excluding cart_data
    $selectAllPaymentsQuery = 'SELECT payment_id, payment_date, bill_status FROM payments';
    $selectAllPaymentsResult = $conn->query($selectAllPaymentsQuery);

    // Check if the query was successful
    if ($selectAllPaymentsResult) {
        $paymentsData = array();

        // Fetch data from the result set
        while ($row = $selectAllPaymentsResult->fetch_assoc()) {
            $paymentsData[] = $row;
        }

        // Reverse the order of the payments data
        $reversedPaymentsData = array_reverse($paymentsData);

        // Always include "data" in the response
        $response['data'] = $reversedPaymentsData;

        // Check if there are payments
        if (empty($reversedPaymentsData)) {
            $response['status'] = false;
            $response['message'] = 'No payments found.';
        } else {
            $response['status'] = true;
            $response['message'] = 'Payments retrieved successfully.';
        }
    } else {
        // Error in the query for payments retrieval
        $response['status'] = false;
        $response['message'] = 'Error in retrieving payments data: ' . $conn->error;
    }
} catch (Exception $e) {
    // Handle any exceptions
    $response['status'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Convert the response array to JSON and echo it
header('Content-Type: application/json');
echo json_encode($response);
?>

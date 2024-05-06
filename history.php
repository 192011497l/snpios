<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Retrieve all history data excluding cart_data, ordered by payment_date in descending order
    $selectAllhistoryQuery = 'SELECT payment_id, payment_date, bill_status FROM history ORDER BY payment_date ASC';
    $selectAllhistoryResult = $conn->query($selectAllhistoryQuery);

    // Check if the query was successful
    if ($selectAllhistoryResult) {
        $historyData = array();

        // Fetch data from the result set
        while ($row = $selectAllhistoryResult->fetch_assoc()) {
            $historyData[] = $row;
        }

        // Reverse the order of the history data
        $reversedHistoryData = array_reverse($historyData);

        // Check if there are history
        if (empty($reversedHistoryData)) {
            $response['status'] = false;
            $response['message'] = 'No history found.';
        } else {
            $response['status'] = true;
            $response['message'] = 'History retrieved successfully in reverse order.';
            $response['Data'] = $reversedHistoryData;
        }
    } else {
        // Error in the query for history retrieval
        $response['status'] = false;
        $response['message'] = 'Error in retrieving history data: ' . $conn->error;
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

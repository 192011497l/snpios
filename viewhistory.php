<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if payment_id is provided in the POST data
        if (isset($_POST['payment_id'])) {
            $paymentId = $_POST['payment_id'];

            // Retrieve specific payment data based on payment_id
            $selecthistoryQuery = 'SELECT * FROM history WHERE payment_id = ?';
            $selecthistoryStmt = $conn->prepare($selecthistoryQuery);

            // Check if prepare() was successful
            if ($selecthistoryStmt) {
                $selecthistoryStmt->bind_param('i', $paymentId);
                $selecthistoryStmt->execute();

                $paymentData = array();

                // Fetch data from the result set
                $result = $selecthistoryStmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $paymentData = $row;
                }

                // Check if payment data is found
                if (!empty($paymentData)) {
                    $cartData = json_decode($paymentData['cart_data'], true);

                    $response['status'] = true;
                    $response['message'] = 'Payment data retrieved successfully.';
                    $response['payment_id'] = $paymentData['payment_id'];
                    $response['payment_date'] = $paymentData['payment_date'];
                    $response['bill_status'] = $paymentData['bill_status'];
                    $response['cart_data'] = $cartData;
                } else {
                    $response['status'] = false;
                    $response['message'] = 'No payment found with the provided payment_id.';
                }
            } else {
                // Error in prepare() for payment select
                $response['status'] = false;
                $response['message'] = 'Error in prepare() for payment select: ' . $conn->error;
            }

            $selecthistoryStmt->close();
        } else {
            // Handle case where payment_id is not provided in the POST data
            $response['status'] = false;
            $response['message'] = 'Payment ID not provided in the request.';
        }
    } else {
        // Handle non-POST requests (e.g., return an error response)
        $response['status'] = false;
        $response['message'] = 'Invalid request method.';
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

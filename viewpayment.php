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
            $selectPaymentQuery = 'SELECT * FROM payments WHERE payment_id = ?';
            $selectPaymentStmt = $conn->prepare($selectPaymentQuery);

            // Check if prepare() was successful
            if ($selectPaymentStmt) {
                $selectPaymentStmt->bind_param('i', $paymentId);
                $selectPaymentStmt->execute();

                $result = $selectPaymentStmt->get_result();

                // Check if payment data is found
                if ($result->num_rows > 0) {
                    $firstPayment = $result->fetch_assoc();

                    // Check if cart_data is not null
                    if ($firstPayment['cart_data'] !== null) {
                        $cartData = json_decode($firstPayment['cart_data'], true);

                        $response['status'] = true;
                        $response['message'] = 'Payment data retrieved successfully.';
                        $response['payment_id'] = $firstPayment['payment_id'];
                        $response['payment_date'] = $firstPayment['payment_date'];
                        $response['bill_status'] = $firstPayment['bill_status'];
                        $response['cart_data'] = $cartData;
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Cart data is null for the provided payment_id.';
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = 'No payment found with the provided payment_id.';
                }
            } else {
                // Error in prepare() for payment select
                $response['status'] = false;
                $response['message'] = 'Error in prepare() for payment select: ' . $conn->error;
            }

            $selectPaymentStmt->close();
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

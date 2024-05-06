<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Retrieve cart data
        $selectCartQuery = 'SELECT * FROM cart';
        $selectCartResult = $conn->query($selectCartQuery);

        // Check if the query was successful
        if ($selectCartResult) {
            $cartdata = array();

            // Fetch data from the result set
            while ($row = $selectCartResult->fetch_assoc()) {
                $cartdata[] = $row;
            }

            // Check if the cart is empty
            if (empty($cartdata)) {
                $response['data'] = [];
                $response['status'] = false;
                $response['message'] = 'Cart is empty. Cannot proceed with payment.';
            } else {
                // Generate a unique payment_id (integer value)
                $paymentId = rand(10000, 99999);

                // Get the current date as payment_date
                $paymentDate = date('Y-m-d');

                // Set the initial bill_status to "waiting"
                $billStatus = 'waiting';

                // Prepare the SQL statement for payment insertion
                $insertPaymentQuery = 'INSERT INTO payments (payment_id, payment_date, cart_data, bill_status) VALUES (?, ?, ?, ?)';
                $insertPaymentStmt = $conn->prepare($insertPaymentQuery);

                // Check if prepare() was successful
                if ($insertPaymentStmt) {
                    // Create a separate variable for JSON-encoded cart data
                    $jsonCartdata = json_encode($cartdata);

                    // Bind parameters and execute the payment insertion statement
                    $insertPaymentStmt->bind_param('isss', $paymentId, $paymentDate, $jsonCartdata, $billStatus);
                    $insertPaymentStmt->execute();

                    // Check if the payment insertion was successful
                    if ($insertPaymentStmt->affected_rows > 0) {
                        $response['data'] = array(
                            array(
                                'payment_id' => $paymentId,
                                'payment_date' => $paymentDate,
                                'bill_status' => $billStatus
                            )
                        );
                        $response['status'] = true;
                        $response['message'] = 'Payment successful. Cart details stored.';
                    
                        // Optionally, remove the cart entries
                        $clearCartQuery = 'DELETE FROM cart';
                        $clearCartResult = $conn->query($clearCartQuery);
                    
                        // Check if cart entries were cleared successfully
                        if (!$clearCartResult) {
                            $response['status'] = false;
                            $response['message'] = 'Failed to clear cart entries: ' . $conn->error;
                        }
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Failed to store cart details.';
                    }

                    $insertPaymentStmt->close();
                } else {
                    // Error in prepare() for payment insert
                    $response['status'] = false;
                    $response['message'] = 'Error in prepare() for payment insert: ' . $conn->error;
                }
            }
        } else {
            // Error in the query for cart retrieval
            $response['status'] = false;
            $response['message'] = 'Error in retrieving cart data: ' . $conn->error;
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

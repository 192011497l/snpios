<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the payment_id parameter is set in the POST data
        if (isset($_POST['payment_id'])) {
            // Sanitize and retrieve the payment_id from the POST data
            $paymentId = $conn->real_escape_string($_POST['payment_id']);

            // Prepare the SQL statement for retrieving specific payment data
            $selectPaymentsQuery = "SELECT * FROM payments WHERE payment_id = '$paymentId'";
            $selectPaymentsResult = $conn->query($selectPaymentsQuery);

            // Check if the query was successful
            if ($selectPaymentsResult) {
                $paymentsData = array();
                $paymentInfo = array(); // Initialize paymentInfo array

                // Fetch data from the result set
                while ($row = $selectPaymentsResult->fetch_assoc()) {
                    // Extract relevant information
                    $paymentInfo[] = array(
                        "payment_id" => $row['payment_id'],
                        "payment_date" => $row['payment_date'],
                        "bill_status" => $row['bill_status']
                    );

                    // Check if bill_status is "waiting" and update it to "completed"
                    if ($row['bill_status'] === 'waiting') {
                        $updateStatusQuery = "UPDATE payments SET bill_status = 'completed' WHERE payment_id = '$paymentId'";
                        if ($conn->query($updateStatusQuery) !== TRUE) {
                            // Error in update
                            $response['status'] = false;
                            $response['message'] = 'Error updating status: ' . $conn->error;
                            error_log($response['message']);  // Add this line for error logging
                            header('Content-Type: application/json');
                            echo json_encode($response);
                            exit;
                        }

                        // Define $paymentDate and $billStatus
                        $paymentDate = $row['payment_date'];
                        $billStatus = 'completed';

                        // Define $cartData
                        $cartData = json_decode($row['cart_data'], true);

                        // Insert data into history table
                        $insertHistoryQuery = 'INSERT INTO history (payment_id, payment_date, cart_data, bill_status) VALUES (?, ?, ?, ?)';
                        $insertHistoryStmt = $conn->prepare($insertHistoryQuery);

                        // Check if prepare() was successful
                        if ($insertHistoryStmt) {
                            // Create a separate variable for JSON-encoded cart data
                            $jsonCartData = json_encode($cartData);

                            // Bind parameters and execute the payment insertion statement
                            $insertHistoryStmt->bind_param('isss', $paymentId, $paymentDate, $jsonCartData, $billStatus);
                            $insertHistoryStmt->execute();

                            // Check if the payment insertion was successful
                            if ($insertHistoryStmt->affected_rows > 0) {
                                $response['status'] = true;
                                $response['message'] = 'Payment successful. Cart details stored.';
                                $response['payment_id'] = $paymentId;
                                $response['payment_date'] = $paymentDate;
                                $response['bill_status'] = $billStatus;

                                // Optionally, remove the cart entries
                                $clearPaymentsQuery = 'DELETE FROM payments';
                                $clearPaymentsResult = $conn->query($clearPaymentsQuery);

                                // Check if cart entries were cleared successfully
                                if (!$clearPaymentsResult) {
                                    $response['status'] = false;
                                    $response['message'] = 'Failed to clear cart entries: ' . $conn->error;
                                }
                            } else {
                                $response['status'] = false;
                                $response['message'] = 'Failed to store cart details.';
                            }

                            $insertHistoryStmt->close();
                        }
                    }

                    // Decode the cart_data JSON string
                    $cartData = json_decode($row['cart_data'], true);

                    // Check if $cartData is not null before looping
                    if ($cartData !== null) {
                        // Add individual items to response
                        foreach ($cartData as $item) {
                            $paymentsData[] = $item;
                        }
                    }
                }

                $response['status'] = true;
                $response['message'] = 'Payment data retrieved successfully.';
                $response['payment_info'] = $paymentInfo;
                $response['data'] = $paymentsData;

            } else {
                // Error in the query for payments retrieval
                $response['status'] = false;
                $response['message'] = 'Error in retrieving payment data: ' . $conn->error;
            }
        } else {
            // If payment_id is not provided in the POST data
            $response['status'] = false;
            $response['message'] = 'Payment ID not specified in the POST data.';
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

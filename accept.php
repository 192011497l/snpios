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
            $acceptedBillStatus = 'Completed'; // Set the desired status when accepting the bill

            // Update the bill_status for the specific payment_id
            $updateBillStatusQuery = 'UPDATE payments SET bill_status = ? WHERE payment_id = ?';
            $updateBillStatusStmt = $conn->prepare($updateBillStatusQuery);

            // Check if prepare() was successful
            if ($updateBillStatusStmt) {
                $updateBillStatusStmt->bind_param('si', $acceptedBillStatus, $paymentId);
                $updateBillStatusStmt->execute();

                // Check if the update was successful
                if ($updateBillStatusStmt->affected_rows > 0) {
                    // Fetch necessary columns for insertion into the history table
                    $selectHistoryDataQuery = 'SELECT payment_id, payment_date, cart_data, bill_status FROM payments WHERE payment_id = ?';
                    $selectHistoryDataStmt = $conn->prepare($selectHistoryDataQuery);

                    if ($selectHistoryDataStmt) {
                        $selectHistoryDataStmt->bind_param('i', $paymentId);
                        $selectHistoryDataStmt->execute();

                        // Fetch data from the result set
                        $result = $selectHistoryDataStmt->get_result();
                        $historyData = $result->fetch_assoc();

                        // Insert details into the history table
                        $insertHistoryQuery = 'INSERT INTO history (payment_id, payment_date, cart_data, bill_status) VALUES (?, ?, ?, ?)';
                        $insertHistoryStmt = $conn->prepare($insertHistoryQuery);

                        if ($insertHistoryStmt) {
                            $insertHistoryStmt->bind_param('isss', $historyData['payment_id'], $historyData['payment_date'], $historyData['cart_data'], $historyData['bill_status']);
                            $insertHistoryStmt->execute();

                            // Check if the insert into history table was successful
                            if ($insertHistoryStmt->affected_rows > 0) {
                                // Insert a notification message
                                $notificationMessage = 'New payment completed for payment ID ' . $paymentId;
                                $insertNotificationQuery = 'INSERT INTO notifications (message) VALUES (?)';
                                $insertNotificationStmt = $conn->prepare($insertNotificationQuery);

                                if ($insertNotificationStmt) {
                                    $insertNotificationStmt->bind_param('s', $notificationMessage);
                                    $insertNotificationStmt->execute();

                                    // Check if the insert into notifications table was successful
                                    if ($insertNotificationStmt->affected_rows > 0) {
                                        // Delete the payment from the payments table
                                        $deletePaymentQuery = 'DELETE FROM payments WHERE payment_id = ?';
                                        $deletePaymentStmt = $conn->prepare($deletePaymentQuery);

                                        if ($deletePaymentStmt) {
                                            $deletePaymentStmt->bind_param('i', $paymentId);
                                            $deletePaymentStmt->execute();

                                            // Check if the delete was successful
                                            if ($deletePaymentStmt->affected_rows > 0) {
                                                $response['status'] = true;
                                                $response['message'] = 'Payment accepted. Details moved to history, notification added, and payment deleted.';
                                            } else {
                                                $response['status'] = false;
                                                $response['message'] = 'Failed to delete payment: ' . $conn->error;
                                            }

                                            $deletePaymentStmt->close();
                                        } else {
                                            $response['status'] = false;
                                            $response['message'] = 'Error in prepare() for deleting payment: ' . $conn->error;
                                        }
                                    } else {
                                        $response['status'] = false;
                                        $response['message'] = 'Failed to insert into notifications: ' . $conn->error;
                                    }

                                    $insertNotificationStmt->close();
                                } else {
                                    $response['status'] = false;
                                    $response['message'] = 'Error in prepare() for inserting into notifications: ' . $conn->error;
                                }
                            } else {
                                $response['status'] = false;
                                $response['message'] = 'Failed to insert into history: ' . $conn->error;
                            }

                            $insertHistoryStmt->close();
                        } else {
                            $response['status'] = false;
                            $response['message'] = 'Error in prepare() for inserting into history: ' . $conn->error;
                        }
                    } else {
                        $response['status'] = false;
                        $response['message'] = 'Error in prepare() for fetching history data: ' . $conn->error;
                    }

                    $selectHistoryDataStmt->close();
                } else {
                    $response['status'] = false;
                    $response['message'] = 'No rows affected. Payment not found with the provided payment_id.';
                }
            } else {
                // Error in prepare() for updating bill_status
                $response['status'] = false;
                $response['message'] = 'Error in prepare() for updating bill_status: ' . $conn->error;
            }

            $updateBillStatusStmt->close();
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

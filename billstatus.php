<?php
// Include the database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scanandpay";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the payment_id and bill_status parameters are set in the POST data
        if (isset($_POST['payment_id']) && isset($_POST['bill_status'])) {
            // Sanitize and retrieve the payment_id and bill_status from the POST data
            $paymentId = $conn->real_escape_string($_POST['payment_id']);
            $billStatus = $conn->real_escape_string($_POST['bill_status']);

            // Fetch cart_data based on payment_id
            $getCartDataQuery = "SELECT cart_data FROM payments WHERE payment_id = '$paymentId'";
            $getCartDataResult = $conn->query($getCartDataQuery);

            // Check if the query was successful
            if ($getCartDataResult) {
                $cartData = $getCartDataResult->fetch_assoc()['cart_data'];

                // Debugging: Display retrieved cart_data (you can remove this in production)
                $response['debug_cart_data'] = $cartData;

                // Prepare the SQL statement for updating bill_status in payments table
                $updatePaymentQuery = "UPDATE payments SET bill_status = '$billStatus' WHERE payment_id = '$paymentId'";
                $updatePaymentResult = $conn->query($updatePaymentQuery);

                // Check if the update in payments table was successful
                if ($updatePaymentResult) {
                    // Debugging: Display the updated bill_status (you can remove this in production)
                    $response['debug_updated_bill_status'] = $billStatus;

                    // Prepare the SQL statement for moving data to payment_history table
                    $insertHistoryQuery = "INSERT INTO history SELECT * FROM payments WHERE payment_id = '$paymentId'";
                    $insertHistoryResult = $conn->query($insertHistoryQuery);

                    // Check if the insert into payment_history table was successful
                    if ($insertHistoryResult) {
                        // Optionally, delete the record from payments table
                        $deletePaymentQuery = "DELETE FROM payments WHERE payment_id = '$paymentId'";
                        $deletePaymentResult = $conn->query($deletePaymentQuery);

                        // Check if the delete from payments table was successful (if needed)
                        // Note: Check $deletePaymentResult if you want to handle this case.

                        $response['status'] = true;
                        $response['message'] = 'Bill status updated, cart data stored, and moved to history successfully.';
                    } else {
                        // Error in moving to payment_history table
                        $response['status'] = false;
                        $response['message'] = 'Error in moving to history table: ' . $conn->error;
                    }
                } else {
                    // Error in updating bill_status in payments table
                    $response['status'] = false;
                    $response['message'] = 'Error in updating bill_status: ' . $conn->error;
                }
            } else {
                // Error in fetching cart_data
                $response['status'] = false;
                $response['message'] = 'Error in fetching cart_data: ' . $conn->error;
            }
        } else {
            // If payment_id or bill_status is not provided in the POST data
            $response['status'] = false;
            $response['message'] = 'Payment ID or Bill Status not specified in the POST data.';
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

<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get input data from the application
        $items_json = $_POST['items'];
        $items = json_decode($items_json, true);

        // Start a transaction to ensure atomicity
        $conn->begin_transaction();

        // Prepare the SQL statement for inserting into bill
        $bill_number = mt_rand(1000, 9999); // Random bill_number (you can adjust the range as needed)
        $bill_date = date('Y-m-d'); // Current date
        $bill_status = 'Unpaid'; // Initial bill status

        $insert_bill_query = 'INSERT INTO bill (bill_number, bill_date, bill_status) VALUES (?, ?, ?)';
        $insert_bill_stmt = $conn->prepare($insert_bill_query);

        // Check if prepare() was successful for bill insertion
        if ($insert_bill_stmt) {
            // Bind parameters and execute the statement for bill insertion
            $insert_bill_stmt->bind_param('dss', $bill_number, $bill_date, $bill_status);
            $insert_bill_stmt->execute();

            // Check if the bill insertion was successful
            if ($insert_bill_stmt->affected_rows > 0) {
                // Get the last inserted bill ID
                $bill_id = $conn->insert_id;

                // Prepare the SQL statement for inserting into cart
                $insert_cart_query = 'INSERT INTO cart (bill_id, name, price, quantity, total_price) VALUES (?, ?, ?, ?, ?)';
                $insert_cart_stmt = $conn->prepare($insert_cart_query);

                // Check if prepare() was successful for cart insertion
                if ($insert_cart_stmt) {
                    foreach ($items as $item) {
                        // Get input data for each item
                        $name = $item['name'];
                        $price = $item['price'];
                        $quantity = $item['quantity'];

                        // Calculate the total price for each item
                        $totalPrice = $price * $quantity;

                        // Bind parameters and execute the statement for cart insertion
                        $insert_cart_stmt->bind_param('dssdd', $bill_id, $name, $price, $quantity, $totalPrice);
                        $insert_cart_stmt->execute();
                    }

                    $response['status'] = true;
                    $response['message'] = 'Items added to cart and bill created successfully.';
                } else {
                    // Error in prepare() for cart insert
                    $response['status'] = false;
                    $response['message'] = 'Error in prepare() for cart insert: ' . $conn->error;
                }

                // Close the cart statement
                $insert_cart_stmt->close();
            } else {
                $response['status'] = false;
                $response['message'] = 'Failed to create bill.';
            }

            // Close the bill statement
            $insert_bill_stmt->close();
        } else {
            // Error in prepare() for bill insert
            $response['status'] = false;
            $response['message'] = 'Error in prepare() for bill insert: ' . $conn->error;
        }

        // Commit the transaction
        $conn->commit();
    } else {
        // Handle non-POST requests (e.g., return an error response)
        $response['status'] = false;
        $response['message'] = 'Invalid request method.';
    }
} catch (Exception $e) {
    // Rollback the transaction in case of an exception
    $conn->rollback();

    // Handle any exceptions
    $response['status'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Convert the response array to JSON and echo it
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$conn->close();
?>

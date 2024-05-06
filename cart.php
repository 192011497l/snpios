<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get input data from the application
        $name = $_POST['name'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];

        // Calculate the total price
        $totalPrice = $price * $quantity;

        // Prepare the SQL statement
        $insert_query = 'INSERT INTO cart (name, price, quantity, total_price) VALUES (?, ?, ?, ?)';
        $insert_stmt = $conn->prepare($insert_query);

        // Check if prepare() was successful
        if ($insert_stmt) {
            // Bind parameters and execute the statement
            $insert_stmt->bind_param('ssdi', $name, $price, $quantity, $totalPrice);
            $insert_stmt->execute();

            // Check if the insertion was successful
            if ($insert_stmt->affected_rows > 0) {
                $response['status'] = true;
                $response['message'] = 'Item added to cart successfully.';
            } else {
                $response['status'] = false;
                $response['message'] = 'Failed to add item to cart.';
            }

            $insert_stmt->close();
        } else {
            // Error in prepare() for insert
            $response['status'] = false;
            $response['message'] = 'Error in prepare() for insert: ' . $conn->error;
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

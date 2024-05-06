<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Prepare the SQL statement to fetch all cart items
        $select_query = 'SELECT * FROM cart';
        $select_stmt = $conn->prepare($select_query);

        // Check if prepare() was successful
        if ($select_stmt) {
            // Execute the statement
            $select_stmt->execute();

            // Get the result set
            $result = $select_stmt->get_result();

            // Fetch the cart details
            $cartDetails = array();
            while ($row = $result->fetch_assoc()) {
                $cartDetails[] = $row;
            }

            // Always include cart_details in the response
            $response['cart_details'] = $cartDetails;

            // Check if cart has items
            if (!empty($cartDetails)) {
                $response['status'] = true;
                $response['message'] = 'Cart details retrieved successfully.';
            } else {
                $response['status'] = false;
                $response['message'] = 'Cart is empty.';
            }

            $select_stmt->close();
        } else {
            // Error in prepare() for select
            $response['status'] = false;
            $response['message'] = 'Error in prepare() for select: ' . $conn->error;
        }
    } else {
        // Handle non-GET requests (e.g., return an error response)
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

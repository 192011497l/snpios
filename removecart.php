<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the s_no from the application
        $s_no = $_POST['s_no'];

        // Prepare the SQL statement to delete the item with the specified s_no
        $delete_query = 'DELETE FROM cart WHERE s_no = ?';
        $delete_stmt = $conn->prepare($delete_query);

        // Check if prepare() was successful
        if ($delete_stmt) {
            // Bind parameter and execute the statement
            $delete_stmt->bind_param('i', $s_no);
            $delete_stmt->execute();

            // Check if the deletion was successful
            if ($delete_stmt->affected_rows > 0) {
                $response['status'] = true;
                $response['message'] = 'Item removed from cart successfully.';
            } else {
                $response['status'] = false;
                $response['message'] = 'Failed to remove item from cart.';
            }

            $delete_stmt->close();
        } else {
            // Error in prepare() for delete
            $response['status'] = false;
            $response['message'] = 'Error in prepare() for delete: ' . $conn->error;
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

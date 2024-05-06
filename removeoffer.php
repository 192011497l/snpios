<?php
require_once('con.php');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form
    $id = isset($_POST['id']) ? $_POST['id'] : '';

    // Check if the employee id is provided
    if (empty($id)) {
        echo json_encode(array('status' => false, 'message' => 'Please provide the Offer id.'));
    } else {
        // Check if the employee exists before attempting to remove
        $check_employee_sql = "SELECT * FROM offers WHERE id = '$id'";
        $check_result = $conn->query($check_employee_sql);

        if ($check_result->num_rows > 0) {
            // Delete the employee data from the database
            $delete_sql = "DELETE FROM offers WHERE id = '$id'";

            if ($conn->query($delete_sql) === TRUE) {
                echo json_encode(array('status' => true, 'message' => 'Offer removed successfully.'));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Error: ' . $conn->error));
            }
        } else {
            echo json_encode(array('status' => false, 'message' => 'Offer not found.'));
        }
    }
} else {
    // Handle non-POST requests (e.g., return an error response)
    echo json_encode(array('status' => false, 'message' => 'Invalid request method.'));
}

// Close the database connection
$conn->close();
?>

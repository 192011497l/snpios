<?php
require_once('con.php');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form
    $id = isset($_POST['id']) ? $_POST['id'] : '';

    // Check if the employee id is provided
    if (empty($id)) {
        echo json_encode(array('status' => false, 'message' => 'Please provide the employee id.'));
    } else {
        // Check if the employee exists before attempting to update
        $check_employee_sql = "SELECT * FROM employeemanagement WHERE id = '$id'";
        $check_result = $conn->query($check_employee_sql);

        if ($check_result->num_rows > 0) {
            // Update the employee status in the database
            $update_sql = "UPDATE employeemanagement SET status = CASE 
                            WHEN status = 'active' THEN 'inactive'
                            WHEN status = 'inactive' THEN 'active'
                          END
                          WHERE id = '$id'";

            if ($conn->query($update_sql) === TRUE) {
                $newStatus = $conn->query("SELECT status FROM employeemanagement WHERE id = '$id'")->fetch_assoc()['status'];
                $message = ($newStatus == 'active') ? 'Employee activated successfully.' : 'Employee deactivated successfully.';
                echo json_encode(array('status' => true, 'message' => $message));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Error: ' . $conn->error));
            }
        } else {
            echo json_encode(array('status' => false, 'message' => 'Employee not found.'));
        }
    }
} else {
    // Handle non-POST requests (e.g., return an error response)
    echo json_encode(array('status' => false, 'message' => 'Invalid request method.'));
}

// Close the database connection
$conn->close();
?>

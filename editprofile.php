<?php
require_once('con.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input data from the application
    $username = $_POST["username"];
    $new_username = isset($_POST["new_username"]) ? $_POST["new_username"] : $username;
    $email = isset($_POST["email"]) ? $_POST["email"] : null;
    $mobile_no = isset($_POST["mobile_no"]) ? $_POST["mobile_no"] : null;
    $shop_name = isset($_POST["shop_name"]) ? $_POST["shop_name"] : null;
    $shop_address = isset($_POST["shop_address"]) ? $_POST["shop_address"] : null;

    // Check if the current username exists in the database
    $check_query = "SELECT * FROM signup WHERE username=?";
    $check_stmt = $conn->prepare($check_query);

    // Check if prepare() was successful
    if ($check_stmt) {
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $existing_data = $result->fetch_assoc();

            // Build the dynamic update query based on provided fields
            $update_params = array();
            $update_query = "UPDATE signup SET ";

            $update_query .= "username=?, ";
            $update_params[] = $new_username;

            if ($email !== null) {
                $update_query .= "email=?, ";
                $update_params[] = $email;
            } else {
                $update_query .= "email=?, ";
                $update_params[] = $existing_data['email'];
            }

            if ($mobile_no !== null) {
                $update_query .= "mobile_no=?, ";
                $update_params[] = $mobile_no;
            } else {
                $update_query .= "mobile_no=?, ";
                $update_params[] = $existing_data['mobile_no'];
            }

            if ($shop_name !== null) {
                $update_query .= "shop_name=?, ";
                $update_params[] = $shop_name;
            } else {
                $update_query .= "shop_name=?, ";
                $update_params[] = $existing_data['shop_name'];
            }

            if ($shop_address !== null) {
                $update_query .= "shop_address=?, ";
                $update_params[] = $shop_address;
            } else {
                $update_query .= "shop_address=?, ";
                $update_params[] = $existing_data['shop_address'];
            }

            // Remove the trailing comma and space
            $update_query = rtrim($update_query, ', ');

            $update_query .= " WHERE username=?";
            $update_params[] = $username;

            // Prepare the dynamic update statement
            $update_stmt = $conn->prepare($update_query);

            // Check if prepare() was successful
            if ($update_stmt) {
                // Dynamically bind parameters
                $param_types = str_repeat('s', count($update_params));
                $update_stmt->bind_param($param_types, ...$update_params);

                if ($update_stmt->execute()) {
                    // Successful update
                    $response = array('status' => true, 'message' => 'Details updated successfully.');
                    echo json_encode($response);
                } else {
                    // Error in database update
                    $response = array('status' => false, 'message' => 'Error: Update failed.');
                    echo json_encode($response);
                }

                $update_stmt->close();
            } else {
                // Error in prepare() for update
                $response = array('status' => false, 'message' => 'Error in prepare() for update.');
                echo json_encode($response);
            }
        } else {
            // User does not exist
            $response = array('status' => false, 'message' => 'User does not exist.');
            echo json_encode($response);
        }

        $check_stmt->close();
    } else {
        // Error in prepare() for check
        $response = array('status' => false, 'message' => 'Error in prepare() for check.');
        echo json_encode($response);
    }
} else {
    // Handle non-POST requests (e.g., return an error response)
    $response = array('status' => false, 'message' => 'Invalid request method.');
    echo json_encode($response);
}

// Close the database connection
$conn->close();
?>

<?php
require_once('con.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input data from the application
    $username = $_POST["username"];
    $email = $_POST["email"];
    $mobile_no = $_POST["mobile_no"];
    $password = ($_POST["password"]) ? $_POST["password"] : null;
    $confirm_password = ($_POST["confirm_password"]) ? $_POST["confirm_password"] : null;

    // Check if the username already exists in signup
    $check_query = "SELECT username FROM usersignup WHERE username=?";
    $check_stmt = $conn->prepare($check_query);

    // Check if prepare() was successful
    if ($check_stmt) {
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // User already exists
            $response = array('status' => false, 'message' => 'User already exists.');
            echo json_encode($response);
        } else {
            // Check if password and confirm_password match
            if ($password !== $confirm_password) {
                $response = array('status' => false, 'message' => 'Password and confirm password do not match.');
                echo json_encode($response);
            } else {
                // Insert data into the signup table
                $insert_query = "INSERT INTO usersignup (username, email, mobile_no, password) VALUES (?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);

                // Check if prepare() was successful
                if ($insert_stmt) {
                    $insert_stmt->bind_param("ssss", $username, $email, $mobile_no,  $password);

                    if ($insert_stmt->execute()) {
                        // Successful insertion
                        $response = array('status' => true, 'message' => 'Registration successful.');
                        echo json_encode($response);
                    } else {
                        // Error in database insertion
                        $response = array('status' => false, 'message' => 'Error: Registration failed.');
                        echo json_encode($response);
                    }

                    $insert_stmt->close();
                } else {
                    // Error in prepare() for insert
                    $response = array('status' => false, 'message' => 'Error in prepare() for insert.');
                    echo json_encode($response);
                }
            }
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

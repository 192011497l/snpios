<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get input data from the application
        $username = $_POST['username'];
        $password = isset($_POST['password']) ? $_POST['password'] : ""; // Set password to empty string if not provided

        // Check if the username exists in signup
        $check_query = 'SELECT id, username, email, password FROM signup WHERE username=?';
        $check_stmt = $conn->prepare($check_query);

        // Check if prepare() was successful
        if ($check_stmt) {
            $check_stmt->bind_param('s', $username);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                // User exists, fetch stored password, id, and email
                $check_stmt->bind_result($userId, $fetchedUsername, $fetchedEmail, $storedPassword);
                $check_stmt->fetch();

                // Verify the entered password against the stored password
                if ($password === $storedPassword) {
                    // Password is correct
                    $response['status'] = true;
                    $response['message'] = 'Login successful.';
                    $response['data'] = array(
                        array(
                            'userId' => $userId,
                            'username' => $fetchedUsername,
                            'email' => $fetchedEmail
                        )
                    );
                } else {
                    // Invalid password
                    $response['status'] = false;
                    $response['message'] = 'Invalid password.';
                    $response['data'] = array(); // Ensure 'data' is always an array
                }
            } else {
                // User does not exist
                $response['status'] = false;
                $response['message'] = 'User does not exist.';
                $response['data'] = array(); // Ensure 'data' is always an array
            }

            $check_stmt->close();
        } else {
            // Error in prepare() for check
            $response['status'] = false;
            $response['message'] = 'Error in prepare() for check: ' . $conn->error;
            $response['data'] = array(); // Ensure 'data' is always an array
        }
    } else {
        // Handle non-POST requests (e.g., return an error response)
        $response['status'] = false;
        $response['message'] = 'Invalid request method.';
        $response['data'] = array(); // Ensure 'data' is always an array
    }
} catch (Exception $e) {
    // Handle any exceptions
    $response['status'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['data'] = array(); // Ensure 'data' is always an array
}

// Convert the response array to JSON and echo it
header('Content-Type: application/json');
echo json_encode($response);
?>

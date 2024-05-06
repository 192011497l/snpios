<?php
require_once('con.php');

$response = array();

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['username'])) {
            $userId = $_POST['username'];

            $sql = "SELECT username, COALESCE(email, '') AS email, COALESCE(mobile_no, '') AS mobile_no FROM usersignup WHERE username = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("s", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result) {
                    $data = $result->fetch_assoc();

                    if ($data) {
                        $response['status'] = true;
                        $response['message'] = "User details retrieved successfully";
                        $response['data'] = array($data);
                    } else {
                        $response['status'] = false;
                        $response['message'] = "User with username $userId not found.";
                        $response['data'] = [];
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = "Query execution failed: " . $stmt->error;
                    $response['data'] = [];
                }

                $stmt->close();
            } else {
                $response['status'] = false;
                $response['message'] = "Failed to prepare SQL statement.";
                $response['data'] = [];
            }
        } else {
            $response['status'] = false;
            $response['message'] = "Username not provided.";
            $response['data'] = [];
        }
    } else {
        $response['status'] = false;
        $response['message'] = "Invalid request method. Only POST requests are allowed.";
        $response['data'] = [];
    }
} catch (Exception $e) {
    // Log the exception to a secure location
    error_log("Exception: " . $e->getMessage());
    
    $response['status'] = false;
    $response['message'] = "An unexpected error occurred. Please try again later.";
    $response['data'] = [];
}

header('Content-Type: application/json');
echo json_encode($response);
?>

<?php
 $servername = "localhost";

    $username = "root";

    $password = "";

    $dbname = "scanandpay"; 
	
	$message = "Register successful";
	
	
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

	 $Name = $_POST['username'];

     $email = $_POST['email'];

     $number = $_POST['mobile_number'];

     $password = $_POST['password'];

     $confirmpassward = $_POST['confirm_password'];


     $sql = "INSERT INTO signup (username, email,password, confirm_password,mobile_no) VALUES('$Name','$email','$password','$confirmpassward','$number')";
	 

if ($conn->query($sql) === TRUE) {
  
  echo "<script type='text/javascript'>alert('$message');window.location.href='signup.php';</script>";
  
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>

///?
<?php
// Include your database connection code here (e.g., db_conn.php)
require_once('con.php');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input data from the application
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $mobile_no = $_POST["mobile_no"];

    // Check if the username already exists in signup
    $check_query = "SELECT username FROM signup WHERE username=?";
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
            // Insert data into the signup table
            $insert_query = "INSERT INTO signup (username, password, email, mobile_no) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);

            // Check if prepare() was successful
            if ($insert_stmt) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt->bind_param("ssss", $username, $hashedPassword, $email, $mobile_no);

                if ($insert_stmt->execute()) {
                    // Successful insertion
                    $response = array('status' => true, 'message' => 'registration successful.');
                    echo json_encode($response);
                } else {
                    // Error in database insertion
                    $response = array('status' => false, 'message' => 'Error: ' . $insert_stmt->error);
                    echo json_encode($response);
                }

                $insert_stmt->close();
            } else {
                // Error in prepare() for insert
                $response = array('status' => false, 'message' => 'Error in prepare() for insert: ' . $conn->error);
                echo json_encode($response);
            }
        }

        $check_stmt->close();
    } else {
        // Error in prepare() for check
        $response = array('status' => false, 'message' => 'Error in prepare() for check: ' . $conn->error);
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







editprofile
<?php
require_once('con.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input data from the application
    $username = $_POST["username"];
    $email = isset($_POST["email"]) ? $_POST["email"] : null;
    $mobile_no = isset($_POST["mobile_no"]) ? $_POST["mobile_no"] : null;
    $shop_name = isset($_POST["shop_name"]) ? $_POST["shop_name"] : null;

    // Check if the username exists in the database
    $check_query = "SELECT * FROM signup WHERE username=?";
    $check_stmt = $conn->prepare($check_query);

    // Check if prepare() was successful
    if ($check_stmt) {
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Get existing values if fields are not provided for update
            $existing_data = $result->fetch_assoc();

            // Build the dynamic update query based on provided fields
            $update_params = array();
            $update_query = "UPDATE signup SET ";
            
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






profile
<?php
// Include the database connection configuration
require_once('con.php');

// Create an associative array to hold the API response
$response = array();

try {
    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Check if the 'id' parameter is set in the request
        if (isset($_POST['id'])) {
            // Get the user ID from the request
            $userId = $_POST['id'];

            // SQL query to fetch user details based on ID using a prepared statement
            $sql = "SELECT username, COALESCE(email, '') AS email, COALESCE(mobile_no, '') AS mobile_no, COALESCE(shop_name, '') AS shop_name FROM signup WHERE id = ?";
            
            // Prepare the statement
            $stmt = $conn->prepare($sql);

            // Bind the parameter
            $stmt->bind_param("i", $userId);

            // Execute the query
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            if ($result) {
                // Fetch the row as an associative array
                $data = $result->fetch_assoc();

                if ($data) {
                    $response['status'] = true;
                    $response['message'] = "Data showing successfully";
                    $response['data'] = array($data);
                } else {
                    $response['status'] = false;
                    $response['message'] = "User with ID $userId not found.";
                    $response['data'] = [];
                }
            } else {
                $response['status'] = false;
                $response['message'] = "Query execution failed: " . $stmt->error;
                $response['data'] = [];
            }

            // Close the statement
            $stmt->close();
        } else {
            $response['status'] = false;
            $response['message'] = "User ID not provided.";
            $response['data'] = [];
        }
    } else {
        $response['status'] = false;
        $response['message'] = "Invalid request method. Only POST requests are allowed.";
        $response['data'] = [];
    }
} catch (Exception $e) {
    // Handle any exceptions
    $response['status'] = false;
    $response['message'] = "Error: " . $e->getMessage();
    $response['data'] = [];
}

// Convert the response array to JSON and echo it
header('Content-Type: application/json');
echo json_encode($response);
?>

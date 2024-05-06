<?php
require_once('con.php');

// Retrieve data from the form
$name = $_POST['name'];
$email = $_POST['email'];
$age = $_POST['age'];
$mobile_no = $_POST['mobile_no'];
$role = $_POST['role'];
$qualification = $_POST['qualification'];
$experience = $_POST['experience'];
$address = $_POST['address'];
$status = $_POST['status'];

// Check if any of the required fields are empty
if (empty($name) || empty($email) || empty($age) || empty($mobile_no) || empty($role) || empty($qualification) || empty($experience) || empty($address)  || empty($status)) {
    echo json_encode(array('status' => false, 'message' => 'Please enter all required fields.'));
} else {
    // Insert the employee data into the database
    $sql = "INSERT INTO employeemanagement (name, email, age, mobile_no, role, qualification, experience, address, status) 
            VALUES ('$name', '$email', '$age', '$mobile_no', '$role', '$qualification', '$experience', '$address', '$status')";

    if ($conn->query($sql) === TRUE) {
        // If the insertion is successful, insert a notification into the database
        // Commented out the notification insertion
        /*
        $notificationMessage = "New employee added: $name";

        // Insert a notification into the database
        $notificationSql = "INSERT INTO notifications (message) VALUES ('$notificationMessage')";
        if ($conn->query($notificationSql) !== TRUE) {
            echo json_encode(array('status' => false, 'message' => 'Error inserting notification: ' . $conn->error));
        } else {
        */
            echo json_encode(array('status' => true, 'message' => 'New employee added.'));
        // Commented out the notification insertion
        /*
        }
        */
    } else {
        echo json_encode(array('status' => false, 'message' => 'Error: ' . $conn->error));
    }
}

// Close the database connection
$conn->close();
?>

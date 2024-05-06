<?php
require_once('con.php');

// Retrieve data from the form
$offer_name = $_POST['offer_name'];
$expiry_date = $_POST['expiry_date'];
$offer_details = $_POST['offer_details'];

// Check if any of the required fields are empty
if (empty($offer_name) || empty($expiry_date) || empty($offer_details)) {
    echo json_encode(array('status' => false, 'message' => 'Please enter all required fields.'));
} else {
    // Insert the employee data into the database
    $sql = "INSERT INTO offers (offer_name, expiry_date, offer_details) 
            VALUES ('$offer_name', '$expiry_date', '$offer_details')";

    if ($conn->query($sql) === TRUE) {
        // If the insertion is successful, insert a notification into the database
        $notificationMessage = "New offer added: $offer_name";

        // Insert a notification into the database
        $notificationSql = "INSERT INTO notifications (message) VALUES ('$notificationMessage')";
        if ($conn->query($notificationSql) !== TRUE) {
            echo json_encode(array('status' => false, 'message' => 'Error inserting notification: ' . $conn->error));
        } else {
            echo json_encode(array('status' => true, 'message' => 'New offer added.'));
        }
    } else {
        echo json_encode(array('status' => false, 'message' => 'Error: ' . $conn->error));
    }
}

// Close the database connection
$conn->close();
?>

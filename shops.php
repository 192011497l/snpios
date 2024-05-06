<?php
require_once('con.php');

$response = array();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $query = 'SELECT shop_name, shop_address FROM signup';
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($shopName, $shopAddress);

                $shops = array();

                while ($stmt->fetch()) {
                    $shops[] = array(
                        'shop_name' => $shopName,
                        'shop_address' => $shopAddress
                    );
                }

                $response['status'] = true;
                $response['message'] = 'Shop information retrieved successfully.';
                $response['data'] = $shops;
            } else {
                $response['status'] = false;
                $response['message'] = 'No shops found.';
                $response['data'] = array();
            }

            $stmt->close();
        } else {
            $response['status'] = false;
            $response['message'] = 'Error in prepare(): ' . $conn->error;
            $response['data'] = array();
        }
    } else {
        $response['status'] = false;
        $response['message'] = 'Invalid request method.';
        $response['data'] = array();
    }
} catch (Exception $e) {
    $response['status'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['data'] = array();
}

header('Content-Type: application/json');
echo json_encode($response);
?>

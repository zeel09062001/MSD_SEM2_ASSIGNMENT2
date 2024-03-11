<?php
session_start();



require 'connection/connection.php';
require 'CRUD_Tables/User.php';
require 'CRUD_Tables/Product.php';
require 'CRUD_Tables/Comment.php';
require 'CRUD_Tables/Cart.php';
require 'CRUD_Tables/Order.php';

$db = new Database();
$conn = $db->getConnection();

function isAuthenticated()
{
    if (isset($_SESSION['user_id'])) {
        return true;
    } else {
        return false;
    }
}

//CRUD for product table
$product = new Product($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_product') {
    header('Content-Type: application/json');
    echo json_encode($product->getAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add_product') {
    $data = json_decode(file_get_contents("php://input"), true);

    $required_fields = ['name', 'description', 'image_url', 'pricing', 'shipping_cost'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required field: $field"));
            exit;
        }
    }

    $id = $product->add($data);

    if ($id !== false) {
        http_response_code(201);
        echo json_encode(array("id" => $id, "message" => "Product added successfully."));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to add product."));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'update_product') {
    $json_data = file_get_contents("php://input");
    if ($json_data === false || !($data = json_decode($json_data, true)) || !isset($data['id'], $data['description'], $data['image_url'], $data['pricing'], $data['shipping_cost'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid or incomplete JSON data."));
        exit;
    }

    try {
        $product->update($data['id'], $data);
        http_response_code(200);
        echo json_encode(array("message" => "Product updated."));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to update product: " . $e->getMessage()));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'delete_product') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Product ID is required."));
        exit;
    }

    $product_id = $_GET['id'];

    try {
        $product->delete($product_id);
        echo json_encode(array("message" => "Product is deleted."));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to delete product. This product may be associated with other records."));
    }
}


//CRUD for user tabel
$userData = new UserData($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_users') {
    header('Content-Type: application/json');
    echo json_encode($userData->getAll());
}

//For user login 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'login') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Missing email or password."));
        exit;
    }

    $user = $userData->getUserByEmail($data['email']);

    if ($user && password_verify($data['password'], $user['password'])) {
        // Password is correct
        http_response_code(200);
        echo json_encode(array("message" => "Login successful."));
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Invalid email or password."));
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add_user') {
    // Proceed with user addition
    $data = json_decode(file_get_contents("php://input"), true);

    $required_fields = ['username', 'password', 'email', 'shipping_address'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required field: $field"));
            exit;
        }
    }

    $id = $userData->add($data);

    if ($id !== false) {
        http_response_code(201);
        echo json_encode(array("user_id" => $id, "message" => "User Registered Successfully!"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Registration failed."));
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'update_user') {
    $json_data = file_get_contents("php://input");
    if ($json_data === false || !($data = json_decode($json_data, true)) || !isset($data['user_id'], $data['username'], $data['password'], $data['email'], $data['shipping_address'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid or incomplete JSON data."));
        exit;
    }

    try {
        $userData->update($data['user_id'], $data);
        http_response_code(200);
        echo json_encode(array("message" => "user updated."));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to update user: " . $e->getMessage()));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'delete_user') {
    if (!isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(array("message" => "User ID is required."));
        exit;
    }

    $user_id = $_GET['user_id'];

    try {
        $userData->delete($user_id);
        echo json_encode(array("message" => "User is deleted."));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to delete user. This user may be associated with other records."));
    }
}


//CRUD for comments table

$commentData = new CommentData($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_comments') {
    header('Content-Type: application/json');
    echo json_encode($commentData->getAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add_comment') {
    $data = json_decode(file_get_contents("php://input"), true);

    $required_fields = ['product_id', 'user_id', 'rating', 'images', 'comment_text'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required field: $field"));
            exit;
        }
    }

    $id = $commentData->add($data);

    if ($id !== false) {
        http_response_code(201);
        echo json_encode(array("comment_id" => $id, "message" => "Comment added successfully."));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to add comment."));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'update_comment') {
    $json_data = file_get_contents("php://input");
    if ($json_data === false || !($data = json_decode($json_data, true)) || !isset($data['comment_id'], $data['product_id'], $data['user_id'], $data['rating'], $data['images'], $data['comment_text'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid or incomplete JSON data."));
        exit;
    }

    try {
        $commentData->update($data['comment_id'], $data);
        http_response_code(200);
        echo json_encode(array("message" => "Comment updated."));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to update comment: " . $e->getMessage()));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'delete_comment') {
    if (!isset($_GET['comment_id'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Comment ID is required."));
        exit;
    }

    $comment_id = $_GET['comment_id'];
    $commentData->delete($comment_id);

    echo json_encode(array("message" => "Comment is deleted."));
}

//CRUD for Cart table

$cartData = new CartData($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_cart') {
    header('Content-Type: application/json');
    echo json_encode($cartData->getAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add_to_cart') {
    $data = json_decode(file_get_contents("php://input"), true);

    $required_fields = ['product_id', 'quantity', 'user_id'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required field: $field"));
            exit;
        }
    }

    $id = $cartData->add($data);

    if ($id !== false) {
        http_response_code(201);
        echo json_encode(array("cart_id" => $id, "message" => "Item added to cart successfully."));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to add item to cart."));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'update_cart_item') {
    $json_data = file_get_contents("php://input");
    if ($json_data === false || !($data = json_decode($json_data, true)) || !isset($data['cart_id'], $data['product_id'], $data['quantity'], $data['user_id'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid or incomplete JSON data."));
        exit;
    }

    try {
        $cartData->update($data['cart_id'], $data);
        http_response_code(200);
        echo json_encode(array("message" => "Cart item updated."));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to update cart item: " . $e->getMessage()));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'remove_from_cart') {
    if (!isset($_GET['cart_id'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Cart ID is required."));
        exit;
    }

    $cart_id = $_GET['cart_id'];
    $cartData->delete($cart_id);

    echo json_encode(array("message" => "Item removed from cart."));
}


//CRUD For order table

$orderData = new OrderData($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_orders') {
    header('Content-Type: application/json');
    echo json_encode($orderData->getAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'place_order') {
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);
    $order_id = $orderData->add($data);

    if ($order_id) {
        $cartData->clearCart($data['user_id']);
        echo json_encode(array("order_id" => $order_id));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to add order."));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'update_order') {
    $json_data = file_get_contents("php://input");
    if ($json_data === false || !($data = json_decode($json_data, true)) || !isset($data['order_id'], $data['user_id'], $data['total_amount'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid or incomplete JSON data."));
        exit;
    }

    try {
        $orderData->update($data['order_id'], $data);
        http_response_code(200);
        echo json_encode(array("message" => "Order updated."));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to update order: " . $e->getMessage()));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'cancel_order') {
    if (!isset($_GET['order_id'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Order ID is required."));
        exit;
    }

    $order_id = $_GET['order_id'];
    $orderData->delete($order_id);

    echo json_encode(array("message" => "Order canceled."));
}

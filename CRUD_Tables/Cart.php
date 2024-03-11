<?php

class CartData
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT cart.*, product.* FROM cart INNER JOIN product ON cart.product_id = product.id");
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($cartItems as $cartItem) {
            $productDetails = [
                'id' => $cartItem['id'],
                'description' => $cartItem['description'],
                'image_url' => $cartItem['image_url'],
                'pricing' => $cartItem['pricing'],
                'shipping_cost' => $cartItem['shipping_cost'],
                'name' => $cartItem['name']
            ];

            $result[] = [
                'cart_id' => $cartItem['cart_id'],
                'product' => $productDetails,
                'quantity' => $cartItem['quantity'],
                'user_id' => $cartItem['user_id']
            ];
        }

        return $result;
    }


    public function add($data)
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO cart (product_id, quantity, user_id) VALUES (?, ?, ?)");
            $stmt->execute([$data['product_id'], $data['quantity'], $data['user_id']]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function update($cart_id, $data)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE cart SET product_id = ?, quantity = ?, user_id = ? WHERE cart_id = ?");
            $stmt->execute([$data['product_id'], $data['quantity'], $data['user_id'], $cart_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($cart_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE cart_id = ?");
        $stmt->execute([$cart_id]);
    }

    public function clearCart($user_id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}

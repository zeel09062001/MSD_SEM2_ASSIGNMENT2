<?php

class OrderData
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT `Order`.*, Users.* FROM `Order` INNER JOIN Users ON `Order`.user_id = Users.user_id");
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($orderItems as $orderItem) {
            // Extract user details
            $userDetails = [
                'user_id' => $orderItem['user_id'],
                'email' => $orderItem['email'],
                'password' => $orderItem['password'],
                'username' => $orderItem['username'],
                'shipping_address' => $orderItem['shipping_address']
            ];
            $result[] = [
                'order_id' => $orderItem['order_id'],
                'user' => $userDetails,
                'total_amount' => $orderItem['total_amount'],
                'order_date' => $orderItem['order_date']
            ];
        }

        return $result;
    }

    public function add($data)
    {
        try {
            // query to add order
            $stmt = $this->conn->prepare("INSERT INTO `Order` (user_id, total_amount) VALUES (?, ?)");
            $stmt->execute([$data['user_id'], $data['total_amount']]);
            $order_id = $this->conn->lastInsertId();

            $cartData = new CartData($this->conn); 
            $cartData->clearCart($data['user_id']);

            return $order_id;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }



    public function update($order_id, $data)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE `Order` SET user_id = ?, total_amount = ? WHERE order_id = ?");
            $stmt->execute([$data['user_id'], $data['total_amount'], $order_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($order_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM `Order` WHERE order_id = ?");
        $stmt->execute([$order_id]);
    }
}

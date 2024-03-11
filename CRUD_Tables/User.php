<?php
class UserData
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT * FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function add($data)
    {
        try {
            // Hash the password before storing it
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, shipping_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['username'], $data['email'], $hashedPassword, $data['shipping_address']]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }


    public function update($user_id, $data)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, shipping_address = ? WHERE user_id = ?");
            $stmt->execute([$data['username'], $data['email'], $data['password'], $data['shipping_address'], $user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
    }

    //Get user detials by it's email
    public function getUserByEmail($email)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    function isAuthenticated()
    {
        if (isset($_SESSION['user_id'])) {
            return true;
        } else {
            return false;
        }
    }
}

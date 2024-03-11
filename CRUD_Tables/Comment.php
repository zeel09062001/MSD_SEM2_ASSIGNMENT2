<?php
class CommentData
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT * FROM comments");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($data)
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO comments (product_id, user_id, rating, images, comment_text) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$data['product_id'], $data['user_id'], $data['rating'], $data['images'], $data['comment_text']]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function update($comment_id, $data)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE comments SET product_id = ?, user_id = ?, rating = ?, images = ?, comment_text = ? WHERE comment_id = ?");
            $stmt->execute([$data['product_id'], $data['user_id'], $data['rating'], $data['images'], $data['comment_text'], $comment_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($comment_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM comments WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
    }
}

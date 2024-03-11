<?php
class Product
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT * FROM Product");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO Product (name, pricing, description, image_url, shipping_cost) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['pricing'], $data['description'], $data['image_url'], $data['shipping_cost']]);
        return $this->conn->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->conn->prepare("UPDATE product SET name = ?, pricing = ?, description = ?, image_url = ?, shipping_cost = ?  WHERE id = ?");
        $stmt->execute([$data['name'], $data['pricing'], $data['description'], $data['image_url'], $data['shipping_cost'], $id]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM product WHERE id = ?");
        $stmt->execute([$id]);
    }
}

<?php
require_once 'config/database.php';

class Designer {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllDesigners() {
        $query = "SELECT HEX(id) AS id, code, name, firm_name, address, contact_no, created_at, profile_bg_color FROM designers";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createDesigner($data) {
        $query = "INSERT INTO designers (id, code, name, firm_name, address, contact_no, profile_bg_color, created_at)
                  VALUES (UNHEX(?), ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($query);
        $id = bin2hex(random_bytes(16));
        $stmt->execute([$id, $data['code'], $data['name'], $data['firm_name'], $data['address'], $data['contact_no'], $data['profile_bg_color']]);
        return $stmt->rowCount();
    }

    public function updateDesigner($data) {
        $query = "UPDATE designers SET code=?, name=?, firm_name=?, address=?, contact_no=?, profile_bg_color=? WHERE id=UNHEX(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$data['code'], $data['name'], $data['firm_name'], $data['address'], $data['contact_no'], $data['profile_bg_color'], $data['id']]);
        return $stmt->rowCount();
    }

    public function deleteDesigner($id) {
        $query = "DELETE FROM designers WHERE id=UNHEX(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
?>

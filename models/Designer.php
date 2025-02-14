<?php
require_once 'config/database.php';

class Designer
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllDesigners()
    {
        try {
            $query = "SELECT HEX(id) AS id, code, name, firm_name, address, contact_no, created_at, profile_bg_color FROM designers";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $designers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$designers) {
                return [];
            }

            return $designers;
        } catch (PDOException $e) {
            error_log("Database Query Error (getAllDesigners): " . $e->getMessage());
            throw new Exception("Database error while fetching designers");
        }
    }

    public function createDesigner($data)
    {
        try {
            if (!isset($data['code'], $data['name'], $data['firm_name'], $data['address'], $data['contact_no'], $data['profile_bg_color'])) {
                throw new Exception("Missing required fields");
            }

            $query = "INSERT INTO designers (id, code, name, firm_name, address, contact_no, profile_bg_color, created_at)
                      VALUES (UNHEX(?), ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($query);
            $id = bin2hex(random_bytes(16));

            if ($stmt->execute([$id, $data['code'], $data['name'], $data['firm_name'], $data['address'], $data['contact_no'], $data['profile_bg_color']])) {
                return true;
            } else {
                throw new Exception("Failed to create designer");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (createDesigner): " . $e->getMessage());
            throw new Exception("Database error while creating designer");
        }
    }

    public function updateDesignerById($id, $data)
    {
        try {
            if (!isset($data['code'], $data['name'], $data['firm_name'], $data['address'], $data['contact_no'], $data['profile_bg_color'])) {
                throw new Exception("Missing required fields");
            }

            $designer = $this->findById($id);
            if (!$designer) {
                throw new Exception("Designer not found");
            }

            $query = "UPDATE designers SET code=?, name=?, firm_name=?, address=?, contact_no=?, profile_bg_color=? WHERE id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$data['code'], $data['name'], $data['firm_name'], $data['address'], $data['contact_no'], $data['profile_bg_color'], $id])) {
                return true;
            } else {
                throw new Exception("No changes made or failed to update designer");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (updateDesignerById): " . $e->getMessage());
            throw new Exception("Database error while updating designer");
        }
    }

    public function deleteDesignerById($id)
    {
        try {
            $designer = $this->findById($id);
            if (!$designer) {
                throw new Exception("Designer not found");
            }

            $query = "DELETE FROM designers WHERE id=UNHEX(?)";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$id])) {
                return true;
            } else {
                throw new Exception("Failed to delete designer");
            }
        } catch (PDOException $e) {
            error_log("Database Query Error (deleteDesignerById): " . $e->getMessage());
            throw new Exception("Database error while deleting designer");
        }
    }

    public function findById($id)
    {
        try {
            $query = "SELECT HEX(id) AS id, code, name, firm_name, address, contact_no, profile_bg_color FROM designers WHERE id = UNHEX(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Query Error (findById): " . $e->getMessage());
            throw new Exception("Database error while fetching designer details");
        }
    }
}
?>

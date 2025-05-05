<?php
namespace App\Models;

require_once __DIR__ . '/../classes/DatabaseUsers.php';

class Teacher
{
    private $db;

    public function __construct()
    {
        $this->db = new \App\DatabaseUsers();
    }

    public function getAll()
    {
        $sql = "SELECT Teach_id, Teach_name, Teach_major, Teach_status, role_ckteach FROM teacher ORDER BY Teach_major, Teach_name";
        return $this->db->query($sql)->fetchAll();
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM teacher WHERE Teach_id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO teacher (Teach_id, Teach_name, Teach_major, Teach_status, role_ckteach) VALUES (:Teach_id, :Teach_name, :Teach_major, :Teach_status, :role_ckteach)";
        $this->db->query($sql, $data);
        return true;
    }

    public function update($id, $data)
    {
        $sql = "UPDATE teacher SET Teach_name = :Teach_name, Teach_major = :Teach_major, Teach_status = :Teach_status, role_ckteach = :role_ckteach WHERE Teach_id = :Teach_id";
        $data['Teach_id'] = $id;
        $this->db->query($sql, $data);
        return true;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM teacher WHERE Teach_id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        // ตรวจสอบว่ามีแถวถูกลบหรือไม่
        return $stmt->rowCount() > 0;
    }
}

<?php
/**
 * Filename: models/Department.php
 * Department Model
 * CRUD for tbl_department
 */

class Department
{
    private $conn;
    private $table_name = "tbl_department";

    public $dept_id;
    public $department_name;
    public $date_entry;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CREATE
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
                  SET department_name = :department_name,
                      date_entry = :date_entry";

        $stmt = $this->conn->prepare($query);

        $this->department_name = htmlspecialchars(strip_tags($this->department_name));
        $this->date_entry = $this->date_entry ?? date('Y-m-d H:i:s');

        $stmt->bindParam(':department_name', $this->department_name);
        $stmt->bindParam(':date_entry', $this->date_entry);

        if ($stmt->execute()) {
            $this->dept_id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // READ paginated
    public function readPaginated($page = 1, $records_per_page = 10, $search = '')
    {
        $offset = ($page - 1) * $records_per_page;

        $query = "SELECT dept_id, department_name, date_entry
                  FROM " . $this->table_name . "
                  WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (department_name LIKE :search)";
        }

        $query .= " ORDER BY date_entry DESC LIMIT :offset, :limit";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $s = "%{$search}%";
            $stmt->bindParam(':search', $s);
        }

        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function countAll($search = '')
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (department_name LIKE :search)";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $s = "%{$search}%";
            $stmt->bindParam(':search', $s);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'] ?? 0;
    }

    // READ one
    public function readOne()
    {
        $query = "SELECT dept_id, department_name, date_entry
                  FROM " . $this->table_name . " WHERE dept_id = :dept_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $this->dept_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->department_name = $row['department_name'];
            $this->date_entry = $row['date_entry'];
            return true;
        }

        return false;
    }

    // UPDATE
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET
                  department_name = :department_name
                  WHERE dept_id = :dept_id";

        $stmt = $this->conn->prepare($query);

        $this->department_name = htmlspecialchars(strip_tags($this->department_name));
        $this->dept_id = htmlspecialchars(strip_tags($this->dept_id));

        $stmt->bindParam(':department_name', $this->department_name);
        $stmt->bindParam(':dept_id', $this->dept_id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // DELETE
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE dept_id = :dept_id";
        $stmt = $this->conn->prepare($query);
        $this->dept_id = htmlspecialchars(strip_tags($this->dept_id));
        $stmt->bindParam(':dept_id', $this->dept_id);

        if ($stmt->execute()) return true;
        return false;
    }
}

?>

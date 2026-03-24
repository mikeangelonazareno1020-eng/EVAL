<?php
/**
 * Filename: models/GradeLevel.php
 * GradeLevel Model
 * Handles all CRUD operations for grade levels
 */

class GradeLevel
{
    private $conn;
    private $table_name = "grade_levels";

    // Object properties
    public $id;
    public $grade_name;
    public $grade_code;
    public $description;
    public $sort_order;
    public $is_active;
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;

    /**
     * Constructor with DB connection
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * CREATE - Add new grade level
     */
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET grade_name = :grade_name,
                      grade_code = :grade_code,
                      description = :description,
                      sort_order = :sort_order,
                      is_active = :is_active,
                      created_by = :created_by";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->grade_name = htmlspecialchars(strip_tags($this->grade_name));
        $this->grade_code = htmlspecialchars(strip_tags($this->grade_code));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->sort_order = htmlspecialchars(strip_tags($this->sort_order));
        $this->is_active = $this->is_active ?? 1;
        $this->created_by = $this->created_by ?? null;

        // Bind values
        $stmt->bindParam(":grade_name", $this->grade_name);
        $stmt->bindParam(":grade_code", $this->grade_code);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":sort_order", $this->sort_order);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":created_by", $this->created_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * READ - Get all grade levels
     */
    public function readAll($active_only = false)
    {
        $query = "SELECT * FROM " . $this->table_name;

        if ($active_only) {
            $query .= " WHERE is_active = 1";
        }

        $query .= " ORDER BY sort_order ASC, grade_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * READ - Get paginated grade levels
     */
    public function readPaginated($page = 1, $records_per_page = 10, $search = '')
    {
        $offset = ($page - 1) * $records_per_page;

        $query = "SELECT * FROM " . $this->table_name;

        if (!empty($search)) {
            $query .= " WHERE grade_name LIKE :search 
                        OR grade_code LIKE :search 
                        OR description LIKE :search";
        }

        $query .= " ORDER BY sort_order ASC, grade_name ASC 
                    LIMIT :offset, :records_per_page";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(":search", $search_term);
        }

        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":records_per_page", $records_per_page, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    /**
     * READ - Get single grade level by ID
     */
    public function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->grade_name = $row['grade_name'];
            $this->grade_code = $row['grade_code'];
            $this->description = $row['description'];
            $this->sort_order = $row['sort_order'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->created_by = $row['created_by'];
            $this->updated_by = $row['updated_by'];
            return true;
        }

        return false;
    }

    /**
     * UPDATE - Update grade level
     */
    public function update()
    {
        $query = "UPDATE " . $this->table_name . "
                  SET grade_name = :grade_name,
                      grade_code = :grade_code,
                      description = :description,
                      sort_order = :sort_order,
                      is_active = :is_active,
                      updated_by = :updated_by
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->grade_name = htmlspecialchars(strip_tags($this->grade_name));
        $this->grade_code = htmlspecialchars(strip_tags($this->grade_code));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->sort_order = htmlspecialchars(strip_tags($this->sort_order));
        $this->is_active = $this->is_active ?? 1;
        $this->updated_by = $this->updated_by ?? null;
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":grade_name", $this->grade_name);
        $stmt->bindParam(":grade_code", $this->grade_code);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":sort_order", $this->sort_order);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":updated_by", $this->updated_by);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * DELETE - Delete grade level
     */
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * SOFT DELETE - Deactivate grade level
     */
    public function softDelete()
    {
        $query = "UPDATE " . $this->table_name . "
                  SET is_active = 0,
                      updated_by = :updated_by
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->updated_by = $this->updated_by ?? null;

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":updated_by", $this->updated_by);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * COUNT - Get total records
     */
    public function countAll($search = '')
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;

        if (!empty($search)) {
            $query .= " WHERE grade_name LIKE :search 
                        OR grade_code LIKE :search 
                        OR description LIKE :search";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(":search", $search_term);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    /**
     * CHECK - Check if grade code exists
     */
    public function gradeCodeExists($exclude_id = null)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE grade_code = :grade_code";

        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $query .= " LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":grade_code", $this->grade_code);

        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * SEARCH - Search grade levels
     */
    public function search($keywords)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE grade_name LIKE :keywords
                  OR grade_code LIKE :keywords
                  OR description LIKE :keywords
                  ORDER BY sort_order ASC, grade_name ASC";

        $stmt = $this->conn->prepare($query);
        $keywords = "%{$keywords}%";
        $stmt->bindParam(":keywords", $keywords);
        $stmt->execute();

        return $stmt;
    }

    /**
     * TOGGLE ACTIVE STATUS
     */
    public function toggleActive()
    {
        $query = "UPDATE " . $this->table_name . "
                  SET is_active = IF(is_active = 1, 0, 1),
                      updated_by = :updated_by
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->updated_by = $this->updated_by ?? null;

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":updated_by", $this->updated_by);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>
<?php
/**
 * Filename: models/Course.php
 * Course Model
 * Handles all CRUD operations for courses
 */

class Course
{
    private $conn;
    private $table_name = "courses";

    // Object properties
    public $id;
    public $course_code;
    public $course_name;
    public $description;
    public $credits;
    public $hours_per_week;
    public $grade_level_id;
    public $department;
    public $is_active;
    public $sort_order;
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
     * CREATE - Add new course
     */
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET course_code = :course_code,
                      course_name = :course_name,
                      description = :description,
                      credits = :credits,
                      hours_per_week = :hours_per_week,
                      grade_level_id = :grade_level_id,
                      department = :department,
                      is_active = :is_active,
                      sort_order = :sort_order,
                      created_by = :created_by";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->course_code = htmlspecialchars(strip_tags($this->course_code));
        $this->course_name = htmlspecialchars(strip_tags($this->course_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->credits = htmlspecialchars(strip_tags($this->credits));
        $this->hours_per_week = htmlspecialchars(strip_tags($this->hours_per_week));
        $this->grade_level_id = $this->grade_level_id ?? null;
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->is_active = $this->is_active ?? 1;
        $this->sort_order = $this->sort_order ?? 0;
        $this->created_by = $this->created_by ?? null;

        // Bind values
        $stmt->bindParam(":course_code", $this->course_code);
        $stmt->bindParam(":course_name", $this->course_name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":credits", $this->credits);
        $stmt->bindParam(":hours_per_week", $this->hours_per_week);
        $stmt->bindParam(":grade_level_id", $this->grade_level_id);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":sort_order", $this->sort_order);
        $stmt->bindParam(":created_by", $this->created_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * READ - Get all courses
     */
    public function readAll($active_only = false)
    {
        $query = "SELECT c.*, g.grade_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN grade_levels g ON c.grade_level_id = g.id";

        if ($active_only) {
            $query .= " WHERE c.is_active = 1";
        }

        $query .= " ORDER BY c.sort_order ASC, c.course_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * READ - Get paginated courses
     */
    public function readPaginated($page = 1, $records_per_page = 10, $search = '', $grade_level_filter = '', $department_filter = '')
    {
        $offset = ($page - 1) * $records_per_page;

        $query = "SELECT c.*, g.grade_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN grade_levels g ON c.grade_level_id = g.id
                  WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (c.course_name LIKE :search 
                        OR c.course_code LIKE :search 
                        OR c.description LIKE :search
                        OR c.department LIKE :search)";
        }

        if (!empty($grade_level_filter)) {
            $query .= " AND c.grade_level_id = :grade_level_filter";
        }

        if (!empty($department_filter)) {
            $query .= " AND c.department = :department_filter";
        }

        $query .= " ORDER BY c.sort_order ASC, c.course_name ASC 
                    LIMIT :offset, :records_per_page";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(":search", $search_term);
        }

        if (!empty($grade_level_filter)) {
            $stmt->bindParam(":grade_level_filter", $grade_level_filter);
        }

        if (!empty($department_filter)) {
            $stmt->bindParam(":department_filter", $department_filter);
        }

        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":records_per_page", $records_per_page, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    /**
     * READ - Get single course by ID
     */
    public function readOne()
    {
        $query = "SELECT c.*, g.grade_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN grade_levels g ON c.grade_level_id = g.id
                  WHERE c.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->course_code = $row['course_code'];
            $this->course_name = $row['course_name'];
            $this->description = $row['description'];
            $this->credits = $row['credits'];
            $this->hours_per_week = $row['hours_per_week'];
            $this->grade_level_id = $row['grade_level_id'];
            $this->department = $row['department'];
            $this->is_active = $row['is_active'];
            $this->sort_order = $row['sort_order'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->created_by = $row['created_by'];
            $this->updated_by = $row['updated_by'];
            return true;
        }

        return false;
    }

    /**
     * UPDATE - Update course
     */
    public function update()
    {
        $query = "UPDATE " . $this->table_name . "
                  SET course_code = :course_code,
                      course_name = :course_name,
                      description = :description,
                      credits = :credits,
                      hours_per_week = :hours_per_week,
                      grade_level_id = :grade_level_id,
                      department = :department,
                      is_active = :is_active,
                      sort_order = :sort_order,
                      updated_by = :updated_by
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->course_code = htmlspecialchars(strip_tags($this->course_code));
        $this->course_name = htmlspecialchars(strip_tags($this->course_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->credits = htmlspecialchars(strip_tags($this->credits));
        $this->hours_per_week = htmlspecialchars(strip_tags($this->hours_per_week));
        $this->grade_level_id = $this->grade_level_id ?? null;
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->is_active = $this->is_active ?? 1;
        $this->sort_order = $this->sort_order ?? 0;
        $this->updated_by = $this->updated_by ?? null;
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":course_code", $this->course_code);
        $stmt->bindParam(":course_name", $this->course_name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":credits", $this->credits);
        $stmt->bindParam(":hours_per_week", $this->hours_per_week);
        $stmt->bindParam(":grade_level_id", $this->grade_level_id);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":sort_order", $this->sort_order);
        $stmt->bindParam(":updated_by", $this->updated_by);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * DELETE - Delete course
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
     * SOFT DELETE - Deactivate course
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
    public function countAll($search = '', $grade_level_filter = '', $department_filter = '')
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (course_name LIKE :search 
                        OR course_code LIKE :search 
                        OR description LIKE :search
                        OR department LIKE :search)";
        }

        if (!empty($grade_level_filter)) {
            $query .= " AND grade_level_id = :grade_level_filter";
        }

        if (!empty($department_filter)) {
            $query .= " AND department = :department_filter";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(":search", $search_term);
        }

        if (!empty($grade_level_filter)) {
            $stmt->bindParam(":grade_level_filter", $grade_level_filter);
        }

        if (!empty($department_filter)) {
            $stmt->bindParam(":department_filter", $department_filter);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    /**
     * CHECK - Check if course code exists
     */
    public function courseCodeExists($exclude_id = null)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE course_code = :course_code";

        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $query .= " LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":course_code", $this->course_code);

        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * SEARCH - Search courses
     */
    public function search($keywords)
    {
        $query = "SELECT c.*, g.grade_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN grade_levels g ON c.grade_level_id = g.id
                  WHERE c.course_name LIKE :keywords
                  OR c.course_code LIKE :keywords
                  OR c.description LIKE :keywords
                  OR c.department LIKE :keywords
                  ORDER BY c.sort_order ASC, c.course_name ASC";

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

    /**
     * GET COURSES BY GRADE LEVEL
     */
    public function getByGradeLevel($grade_level_id)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE grade_level_id = :grade_level_id
                  AND is_active = 1
                  ORDER BY sort_order ASC, course_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":grade_level_id", $grade_level_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * GET DEPARTMENTS - Get unique departments
     */
    public function getDepartments()
    {
        $query = "SELECT DISTINCT department 
                  FROM " . $this->table_name . "
                  WHERE department IS NOT NULL AND department != ''
                  ORDER BY department ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * GET COURSES BY DEPARTMENT
     */
    public function getByDepartment($department)
    {
        $query = "SELECT c.*, g.grade_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN grade_levels g ON c.grade_level_id = g.id
                  WHERE c.department = :department
                  AND c.is_active = 1
                  ORDER BY c.sort_order ASC, c.course_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":department", $department);
        $stmt->execute();

        return $stmt;
    }
}
?>
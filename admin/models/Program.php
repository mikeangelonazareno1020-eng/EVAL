<?php
/**
 * Filename: models/Program.php
 * Program Model
 * Handles all CRUD operations for programs
 */

class Program
{
    private $conn;
    private $table_name = "programs";
    private $junction_table = "program_courses";

    // Object properties
    public $id;
    public $program_code;
    public $program_name;
    public $description;
    public $program_type;
    public $duration_years;
    public $total_credits;
    public $department;
    public $grade_level_id;
    public $is_active;
    public $start_date;
    public $end_date;
    public $max_students;
    public $tuition_fee;
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
     * CREATE - Add new program
     */
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET program_code = :program_code,
                      program_name = :program_name,
                      description = :description,
                      program_type = :program_type,
                      duration_years = :duration_years,
                      total_credits = :total_credits,
                      department = :department,
                      grade_level_id = :grade_level_id,
                      is_active = :is_active,
                      start_date = :start_date,
                      end_date = :end_date,
                      max_students = :max_students,
                      tuition_fee = :tuition_fee,
                      sort_order = :sort_order,
                      created_by = :created_by";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->program_code = htmlspecialchars(strip_tags($this->program_code));
        $this->program_name = htmlspecialchars(strip_tags($this->program_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->program_type = htmlspecialchars(strip_tags($this->program_type));
        $this->duration_years = $this->duration_years ?? 0;
        $this->total_credits = $this->total_credits ?? 0;
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->grade_level_id = $this->grade_level_id ?? null;
        $this->is_active = $this->is_active ?? 1;
        $this->start_date = $this->start_date ?? null;
        $this->end_date = $this->end_date ?? null;
        $this->max_students = $this->max_students ?? 0;
        $this->tuition_fee = $this->tuition_fee ?? 0.00;
        $this->sort_order = $this->sort_order ?? 0;
        $this->created_by = $this->created_by ?? null;

        // Bind values
        $stmt->bindParam(":program_code", $this->program_code);
        $stmt->bindParam(":program_name", $this->program_name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":program_type", $this->program_type);
        $stmt->bindParam(":duration_years", $this->duration_years);
        $stmt->bindParam(":total_credits", $this->total_credits);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":grade_level_id", $this->grade_level_id);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":max_students", $this->max_students);
        $stmt->bindParam(":tuition_fee", $this->tuition_fee);
        $stmt->bindParam(":sort_order", $this->sort_order);
        $stmt->bindParam(":created_by", $this->created_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * READ - Get all programs
     */
    public function readAll($active_only = false)
    {
        $query = "SELECT p.*, g.grade_name,
                  (SELECT COUNT(*) FROM " . $this->junction_table . " WHERE program_id = p.id) as course_count
                  FROM " . $this->table_name . " p
                  LEFT JOIN grade_levels g ON p.grade_level_id = g.id";

        if ($active_only) {
            $query .= " WHERE p.is_active = 1";
        }

        $query .= " ORDER BY p.sort_order ASC, p.program_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * READ - Get paginated programs
     */
    public function readPaginated($page = 1, $records_per_page = 10, $search = '', $program_type_filter = '', $department_filter = '')
    {
        $offset = ($page - 1) * $records_per_page;

        $query = "SELECT p.*, g.grade_name,
                  (SELECT COUNT(*) FROM " . $this->junction_table . " WHERE program_id = p.id) as course_count
                  FROM " . $this->table_name . " p
                  LEFT JOIN grade_levels g ON p.grade_level_id = g.id
                  WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (p.program_name LIKE :search 
                        OR p.program_code LIKE :search 
                        OR p.description LIKE :search
                        OR p.department LIKE :search)";
        }

        if (!empty($program_type_filter)) {
            $query .= " AND p.program_type = :program_type_filter";
        }

        if (!empty($department_filter)) {
            $query .= " AND p.department = :department_filter";
        }

        $query .= " ORDER BY p.sort_order ASC, p.program_name ASC 
                    LIMIT :offset, :records_per_page";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(":search", $search_term);
        }

        if (!empty($program_type_filter)) {
            $stmt->bindParam(":program_type_filter", $program_type_filter);
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
     * READ - Get single program by ID
     */
    public function readOne()
    {
        $query = "SELECT p.*, g.grade_name,
                  (SELECT COUNT(*) FROM " . $this->junction_table . " WHERE program_id = p.id) as course_count
                  FROM " . $this->table_name . " p
                  LEFT JOIN grade_levels g ON p.grade_level_id = g.id
                  WHERE p.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->program_code = $row['program_code'];
            $this->program_name = $row['program_name'];
            $this->description = $row['description'];
            $this->program_type = $row['program_type'];
            $this->duration_years = $row['duration_years'];
            $this->total_credits = $row['total_credits'];
            $this->department = $row['department'];
            $this->grade_level_id = $row['grade_level_id'];
            $this->is_active = $row['is_active'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->max_students = $row['max_students'];
            $this->tuition_fee = $row['tuition_fee'];
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
     * UPDATE - Update program
     */
    public function update()
    {
        $query = "UPDATE " . $this->table_name . "
                  SET program_code = :program_code,
                      program_name = :program_name,
                      description = :description,
                      program_type = :program_type,
                      duration_years = :duration_years,
                      total_credits = :total_credits,
                      department = :department,
                      grade_level_id = :grade_level_id,
                      is_active = :is_active,
                      start_date = :start_date,
                      end_date = :end_date,
                      max_students = :max_students,
                      tuition_fee = :tuition_fee,
                      sort_order = :sort_order,
                      updated_by = :updated_by
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->program_code = htmlspecialchars(strip_tags($this->program_code));
        $this->program_name = htmlspecialchars(strip_tags($this->program_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->program_type = htmlspecialchars(strip_tags($this->program_type));
        $this->duration_years = $this->duration_years ?? 0;
        $this->total_credits = $this->total_credits ?? 0;
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->grade_level_id = $this->grade_level_id ?? null;
        $this->is_active = $this->is_active ?? 1;
        $this->start_date = $this->start_date ?? null;
        $this->end_date = $this->end_date ?? null;
        $this->max_students = $this->max_students ?? 0;
        $this->tuition_fee = $this->tuition_fee ?? 0.00;
        $this->sort_order = $this->sort_order ?? 0;
        $this->updated_by = $this->updated_by ?? null;
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":program_code", $this->program_code);
        $stmt->bindParam(":program_name", $this->program_name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":program_type", $this->program_type);
        $stmt->bindParam(":duration_years", $this->duration_years);
        $stmt->bindParam(":total_credits", $this->total_credits);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":grade_level_id", $this->grade_level_id);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":max_students", $this->max_students);
        $stmt->bindParam(":tuition_fee", $this->tuition_fee);
        $stmt->bindParam(":sort_order", $this->sort_order);
        $stmt->bindParam(":updated_by", $this->updated_by);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * DELETE - Delete program
     */
    public function delete()
    {
        // This will also delete related program_courses due to CASCADE
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
     * SOFT DELETE - Deactivate program
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
    public function countAll($search = '', $program_type_filter = '', $department_filter = '')
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (program_name LIKE :search 
                        OR program_code LIKE :search 
                        OR description LIKE :search
                        OR department LIKE :search)";
        }

        if (!empty($program_type_filter)) {
            $query .= " AND program_type = :program_type_filter";
        }

        if (!empty($department_filter)) {
            $query .= " AND department = :department_filter";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(":search", $search_term);
        }

        if (!empty($program_type_filter)) {
            $stmt->bindParam(":program_type_filter", $program_type_filter);
        }

        if (!empty($department_filter)) {
            $stmt->bindParam(":department_filter", $department_filter);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    /**
     * CHECK - Check if program code exists
     */
    public function programCodeExists($exclude_id = null)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE program_code = :program_code";

        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $query .= " LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":program_code", $this->program_code);

        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * SEARCH - Search programs
     */
    public function search($keywords)
    {
        $query = "SELECT p.*, g.grade_name,
                  (SELECT COUNT(*) FROM " . $this->junction_table . " WHERE program_id = p.id) as course_count
                  FROM " . $this->table_name . " p
                  LEFT JOIN grade_levels g ON p.grade_level_id = g.id
                  WHERE p.program_name LIKE :keywords
                  OR p.program_code LIKE :keywords
                  OR p.description LIKE :keywords
                  OR p.department LIKE :keywords
                  ORDER BY p.sort_order ASC, p.program_name ASC";

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
     * GET PROGRAM COURSES - Get all courses in a program
     */
    public function getProgramCourses($program_id)
    {
        $query = "SELECT pc.*, c.course_code, c.course_name, c.credits, c.description
                  FROM " . $this->junction_table . " pc
                  INNER JOIN courses c ON pc.course_id = c.id
                  WHERE pc.program_id = :program_id
                  ORDER BY pc.semester ASC, pc.sort_order ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":program_id", $program_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * ADD COURSE TO PROGRAM
     */
    public function addCourseToProgram($program_id, $course_id, $semester = 1, $is_required = 1, $sort_order = 0)
    {
        $query = "INSERT INTO " . $this->junction_table . "
                  SET program_id = :program_id,
                      course_id = :course_id,
                      semester = :semester,
                      is_required = :is_required,
                      sort_order = :sort_order";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":program_id", $program_id);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->bindParam(":semester", $semester);
        $stmt->bindParam(":is_required", $is_required);
        $stmt->bindParam(":sort_order", $sort_order);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * REMOVE COURSE FROM PROGRAM
     */
    public function removeCourseFromProgram($program_id, $course_id)
    {
        $query = "DELETE FROM " . $this->junction_table . "
                  WHERE program_id = :program_id AND course_id = :course_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":program_id", $program_id);
        $stmt->bindParam(":course_id", $course_id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * UPDATE PROGRAM COURSE
     */
    public function updateProgramCourse($id, $semester, $is_required, $sort_order)
    {
        $query = "UPDATE " . $this->junction_table . "
                  SET semester = :semester,
                      is_required = :is_required,
                      sort_order = :sort_order
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":semester", $semester);
        $stmt->bindParam(":is_required", $is_required);
        $stmt->bindParam(":sort_order", $sort_order);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
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
     * GET PROGRAM TYPES - Get all program types
     */
    public function getProgramTypes()
    {
        $query = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'program_type'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Extract ENUM values
        preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
        $enum = explode("','", $matches[1]);

        return $enum;
    }
}
?>
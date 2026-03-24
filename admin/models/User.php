<?php
/**
 * Filename: models/User.php
 * User Model
 * Basic CRUD for tbl_users
 */

class User
{
    private $conn;
    private $table_name = "tbl_users";

    public $id;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $email;
    public $password;
    public $role;
    public $profile;
    public $branch;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CREATE
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
                  SET first_name = :first_name,
                      middle_name = :middle_name,
                      last_name = :last_name,
                      email = :email,
                      password = :password,
                      role = :role,
                      profile = :profile,
                      branch = :branch";

        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->middle_name = htmlspecialchars(strip_tags($this->middle_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->profile = htmlspecialchars(strip_tags($this->profile));
        $this->branch = htmlspecialchars(strip_tags($this->branch));

        // Hash password if provided
        $hashed = null;
        if (!empty($this->password)) {
            $hashed = password_hash($this->password, PASSWORD_DEFAULT);
        }

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':middle_name', $this->middle_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $hashed);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':profile', $this->profile);
        $stmt->bindParam(':branch', $this->branch);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // READ paginated
    public function readPaginated($page = 1, $records_per_page = 10, $search = '')
    {
        $offset = ($page - 1) * $records_per_page;

        $query = "SELECT id, first_name, middle_name, last_name, email, role, profile, branch
                  FROM " . $this->table_name . "
                  WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (first_name LIKE :search OR middle_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR role LIKE :search OR branch LIKE :search)";
        }

        $query .= " ORDER BY last_name ASC, first_name ASC LIMIT :offset, :limit";

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
            $query .= " AND (first_name LIKE :search OR middle_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR role LIKE :search OR branch LIKE :search)";
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
        $query = "SELECT id, first_name, middle_name, last_name, email, role, profile, branch, password
                  FROM " . $this->table_name . " WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->first_name = $row['first_name'];
            $this->middle_name = $row['middle_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->profile = $row['profile'];
            $this->branch = $row['branch'];
            $this->password = $row['password'];
            return true;
        }

        return false;
    }

    // UPDATE
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET
                  first_name = :first_name,
                  middle_name = :middle_name,
                  last_name = :last_name,
                  email = :email,
                  role = :role,
                  profile = :profile,
                  branch = :branch";

        // If password provided, update it
        if (!empty($this->password)) {
            $query .= ", password = :password";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->middle_name = htmlspecialchars(strip_tags($this->middle_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->profile = htmlspecialchars(strip_tags($this->profile));
        $this->branch = htmlspecialchars(strip_tags($this->branch));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':middle_name', $this->middle_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':profile', $this->profile);
        $stmt->bindParam(':branch', $this->branch);

        if (!empty($this->password)) {
            $hashed = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashed);
        }

        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // DELETE
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) return true;
        return false;
    }
}

?>

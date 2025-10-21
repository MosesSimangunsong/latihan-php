<?php
require_once (__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASSWORD);
        if (!$this->conn) {
            die('Koneksi database gagal');
        }
    }

    /**
     * Mengambil semua todos dengan filter dan search (Fitur 2 & 3)
     * Diurutkan berdasarkan sort_order (Fitur 6)
     */
    public function getAllTodos($filter = 'all', $search = '')
    {
        // Query dasar
        $query = 'SELECT * FROM todo';
        $params = [];
        $conditions = [];
        $paramIndex = 1;

        // --- Fitur 2: Filter ---
        if ($filter === 'finished') {
            $conditions[] = 'is_finished = TRUE';
        } elseif ($filter === 'unfinished') {
            $conditions[] = 'is_finished = FALSE';
        }
        // 'all' tidak menambahkan kondisi

        // --- Fitur 3: Search ---
        if (!empty($search)) {
            $conditions[] = '(title ILIKE $' . $paramIndex . ' OR description ILIKE $' . $paramIndex . ')';
            $params[] = '%' . $search . '%'; // Tambahkan wildcard
            $paramIndex++;
        }

        // Gabungkan semua kondisi dengan 'AND'
        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        // --- Fitur 6: Pengurutan ---
        $query .= ' ORDER BY sort_order ASC, created_at DESC';

        $result = pg_query_params($this->conn, $query, $params);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $row['is_finished'] = ($row['is_finished'] === 't');
                $todos[] = $row;
            }
        }
        return $todos;
    }

    /**
     * Fitur 1 & 6: Update createTodo dengan sort_order
     */
    public function createTodo($title, $description)
    {
        // Menambahkan sort_order sebagai MAX + 1
        $query = 'INSERT INTO todo (title, description, sort_order) 
                  VALUES ($1, $2, (SELECT COALESCE(MAX(sort_order), 0) + 1 FROM todo))';
        $result = pg_query_params($this->conn, $query, [$title, $description]);
        return $result !== false;
    }

    /**
     * Fitur 1: Update updateTodo
     */
    public function updateTodo($id, $title, $description, $is_finished)
    {
        $is_finished_bool = ($is_finished === 'true') ? 'TRUE' : 'FALSE';
        
        $query = 'UPDATE todo SET title=$1, description=$2, is_finished=$3 WHERE id=$4';
        $result = pg_query_params($this->conn, $query, [$title, $description, $is_finished_bool, $id]);
        return $result !== false;
    }

    public function deleteTodo($id)
    {
        $query = 'DELETE FROM todo WHERE id=$1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return $result !== false;
    }

    /**
     * Fitur 4: Memeriksa apakah judul sudah ada
     */
    public function isTitleExists($title, $excludeId = null)
    {
        $query = 'SELECT COUNT(*) FROM todo WHERE title = $1';
        $params = [$title];
        
        if ($excludeId !== null) {
            $query .= ' AND id != $2';
            $params[] = $excludeId;
        }

        $result = pg_query_params($this->conn, $query, $params);
        if ($result) {
            $row = pg_fetch_row($result);
            return $row[0] > 0;
        }
        return false;
    }

    /**
     * Fitur 5: Mengambil satu todo berdasarkan ID
     * (INI METHOD YANG HILANG)
     */
    public function getTodoById($id)
    {
        $query = 'SELECT * FROM todo WHERE id = $1';
        $result = pg_query_params($this->conn, $query, [$id]);
        
        if ($result && pg_num_rows($result) > 0) {
            $todo = pg_fetch_assoc($result);
            // Konversi boolean
            $todo['is_finished'] = ($todo['is_finished'] === 't');
            return $todo;
        }
        return null;
    }

    /**
     * Fitur 6: Menyimpan urutan baru dari drag-and-drop
     */
    public function updateSorting($idArray)
    {
        if (empty($idArray)) {
            return false;
        }
        
        $query = 'UPDATE todo SET sort_order = CASE id ';
        $params = [];
        $paramIndex = 1;
        
        foreach ($idArray as $sortOrder => $id) {
            $query .= 'WHEN $' . $paramIndex++ . ' THEN $' . $paramIndex++ . ' ';
            $params[] = $id;
            $params[] = $sortOrder + 1;
        }
        
        $inPlaceholders = [];
        foreach ($idArray as $id) {
            $inPlaceholders[] = '$' . $paramIndex++;
            $params[] = $id;
        }

        $query .= 'END WHERE id IN (' . implode(', ', $inPlaceholders) . ')';

        $result = pg_query_params($this->conn, $query, $params);
        return $result !== false;
    }
}
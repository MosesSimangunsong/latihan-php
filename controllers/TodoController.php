<?php
require_once (__DIR__ . '/../models/TodoModel.php');

class TodoController
{
    /**
     * Menampilkan daftar todo (halaman utama)
     * Termasuk filter dan search
     */
    public function index()
    {
        // Fitur 2 & 3: Ambil nilai filter dan search dari URL
        $filter = $_GET['filter'] ?? 'all'; // Default 'all'
        $search = $_GET['search'] ?? '';     // Default '' (kosong)

        $todoModel = new TodoModel();
        // Teruskan parameter filter dan search ke model
        $todos = $todoModel->getAllTodos($filter, $search);
        
        // Kirim semua variabel (termasuk filter & search) ke View
        include (__DIR__ . '/../views/TodoView.php');
    }

    /**
     * Menangani pembuatan todo baru
     * Termasuk validasi (Fitur 4)
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $todoModel = new TodoModel();

            // --- Fitur 4: Validasi ---
            if ($todoModel->isTitleExists($title)) {
                // Atur pesan error dan redirect
                $_SESSION['error_message'] = 'Gagal menambah data. Judul "' . htmlspecialchars($title) . '" sudah ada!';
            } else {
                // Jika tidak ada, baru create
                $todoModel->createTodo($title, $description);
                $_SESSION['success_message'] = 'Data todo berhasil ditambahkan!';
            }
            // ---------------------------
        }
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
    }

    /**
     * Menangani update todo
     * Termasuk validasi (Fitur 4)
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $is_finished = $_POST['is_finished'];
            
            $todoModel = new TodoModel();

            // --- Fitur 4: Validasi ---
            // Cek judul, tapi abaikan ID todo ini sendiri
            if ($todoModel->isTitleExists($title, $id)) {
                // Atur pesan error dan redirect
                $_SESSION['error_message'] = 'Gagal memperbarui data. Judul "' . htmlspecialchars($title) . '" sudah ada!';
            } else {
                // Jika tidak ada, baru update
                $todoModel->updateTodo($id, $title, $description, $is_finished);
                $_SESSION['success_message'] = 'Data todo berhasil diperbarui!';
            }
            // ---------------------------
        }
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
    }

    /**
     * Menangani penghapusan todo
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $todoModel = new TodoModel();
            $todoModel->deleteTodo($id);
            $_SESSION['success_message'] = 'Data todo berhasil dihapus!';
        }
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
    }

    /**
     * Fitur 5: Menampilkan halaman detail
     */
    public function show()
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $todoModel = new TodoModel();
            $todo = $todoModel->getTodoById($id);

            if ($todo) {
                // Jika todo ditemukan, tampilkan view detail
                include (__DIR__ . '/../views/TodoDetailView.php');
            } else {
                // Jika tidak, kembali ke index dengan error
                $_SESSION['error_message'] = 'Todo tidak ditemukan!';
                header('Location: index.php');
            }
        } else {
            $_SESSION['error_message'] = 'ID Todo tidak valid!';
            header('Location: index.php');
        }
    }

    /**
     * Fitur 6: Menerima request AJAX untuk menyimpan urutan
     */
    public function saveSortOrder()
    {
        // Pastikan ini adalah POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        // Ambil data JSON mentah dari body request
        $jsonPayload = file_get_contents('php://input');
        $data = json_decode($jsonPayload, true);

        if (empty($data['order']) || !is_array($data['order'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Data urutan tidak valid']);
            return;
        }

        $todoModel = new TodoModel();
        $success = $todoModel->updateSorting($data['order']);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Urutan berhasil disimpan']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan urutan']);
        }
    }
}
<!DOCTYPE html>
<html>
<head>
    <title>PHP - Aplikasi Todolist</title>
    <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container-fluid p-5">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Todo List</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodo">Tambah Data</button>
            </div>
            <hr />

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); // Hapus pesan setelah ditampilkan ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); // Hapus pesan setelah ditampilkan ?>
            <?php endif; ?>

            <form action="index.php" method="GET" class="row g-3 mb-4 align-items-end">
                <input type="hidden" name="page" value="index">
                <div class="col-md-4">
                    <label for="filter" class="form-label">Filter Status</label>
                    <select name="filter" id="filter" class="form-select" onchange="this.form.submit()">
                        <option value="all" <?= ($filter === 'all') ? 'selected' : '' ?>>Semua</option>
                        <option value="unfinished" <?= ($filter === 'unfinished') ? 'selected' : '' ?>>Belum Selesai</option>
                        <option value="finished" <?= ($filter === 'finished') ? 'selected' : '' ?>>Selesai</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="search" class="form-label">Cari (Judul/Deskripsi)</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Ketik judul atau deskripsi..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Cari</button>
                </div>
            </form>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Judul</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tanggal Dibuat</th>
                        <th scope="col">Tanggal Diperbarui</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody id="sortable-list">
                <?php if (!empty($todos)): ?>
                    <?php foreach ($todos as $i => $todo): ?>
                    <tr data-id="<?= $todo['id'] ?>">
                        <td><?= $i + 1 ?></td>
                        <td>
                            <?= htmlspecialchars($todo['title']) ?>
                            <?php if (!empty($todo['description'])): ?>
                                <small class="d-block text-muted"><?= htmlspecialchars($todo['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($todo['is_finished']): ?>
                                <span class="badge bg-success">Selesai</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Belum Selesai</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d F Y - H:i', strtotime($todo['created_at'])) ?></td>
                        <td><?= date('d F Y - H:i', strtotime($todo['updated_at'])) ?></td>
                        <td>
                            <a href="?page=show&id=<?= $todo['id'] ?>" class="btn btn-sm btn-info">
                                Detail
                            </a>

                            <button class="btn btn-sm btn-warning"
                                onclick="showModalEditTodo(
                                    <?= $todo['id'] ?>,
                                    '<?= htmlspecialchars(addslashes($todo['title'])) ?>',
                                    '<?= htmlspecialchars(addslashes($todo['description'] ?? '')) ?>',
                                    <?= $todo['is_finished'] ? 'true' : 'false' ?>
                                )">
                                Ubah
                            </button>
                            <button class="btn btn-sm btn-danger"
                                onclick="showModalDeleteTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['title'])) ?>')">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada data tersedia!</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addTodo" tabindex="-1" aria-labelledby="addTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTodoLabel">Tambah Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?page=create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputTitle" class="form-label">Judul</label>
                        <input type="text" name="title" class="form-control" id="inputTitle"
                            placeholder="Contoh: Belajar membuat aplikasi website sederhana" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputDescription" class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" id="inputDescription" rows="3"
                            placeholder="Contoh: Memperbarui database, model, controller, dan view..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editTodo" tabindex="-1" aria-labelledby="editTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTodoLabel">Ubah Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?page=update" method="POST">
                <input name="id" type="hidden" id="inputEditTodoId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputEditTitle" class="form-label">Judul</label>
                        <input type="text" name="title" class="form-control" id="inputEditTitle"
                            placeholder="Contoh: Belajar membuat aplikasi website sederhana" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputEditDescription" class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" id="inputEditDescription" rows="3"
                            placeholder="Contoh: Memperbarui database, model, controller, dan view..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="selectEditIsFinished" class="form-label">Status</label>
                        <select class="form-select" name="is_finished" id="selectEditIsFinished">
                            <option value="false">Belum Selesai</option>
                            <option value="true">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteTodo" tabindex="-1" aria-labelledby="deleteTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTodoLabel">Hapus Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    Kamu akan menghapus todo <strong class="text-danger" id="deleteTodoTitle"></strong>.
                    Apakah kamu yakin?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a id="btnDeleteTodo" class="btn btn-danger">Ya, Tetap Hapus</a>
            </div>
        </div>
    </div>
</div>

<script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
// FITUR 1: Update fungsi JavaScript
function showModalEditTodo(todoId, title, description, isFinished) {
    document.getElementById("inputEditTodoId").value = todoId;
    document.getElementById("inputEditTitle").value = title;
    document.getElementById("inputEditDescription").value = description;
    // 'isFinished' adalah boolean, ubah jadi string 'true'/'false' untuk value select
    document.getElementById("selectEditIsFinished").value = isFinished ? 'true' : 'false';
    var myModal = new bootstrap.Modal(document.getElementById("editTodo"));
    myModal.show();
}

function showModalDeleteTodo(todoId, title) {
    document.getElementById("deleteTodoTitle").innerText = title;
    // Ambil filter & search dari URL saat ini dan tambahkan ke link delete
    // agar tidak me-reset filter setelah menghapus
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') ?? 'all';
    const search = urlParams.get('search') ?? '';
    
    document.getElementById("btnDeleteTodo").setAttribute("href", 
        `?page=delete&id=${todoId}&filter=${filter}&search=${search}`
    );
    var myModal = new bootstrap.Modal(document.getElementById("deleteTodo"));
    myModal.show();
}
</script>

<script>
    // Ambil elemen tbody
    var el = document.getElementById('sortable-list');
    
    // Inisialisasi Sortable
    var sortable = Sortable.create(el, {
        animation: 150, // Animasi
        handle: 'tr',   // Target drag adalah baris <tr>
        onEnd: function (evt) {
            // Fungsi ini dipanggil setelah drag selesai
            
            // 1. Dapatkan semua ID todo dalam urutan baru
            var itemIds = [];
            // Ambil semua baris di tbody
            var rows = el.getElementsByTagName('tr');
            for (var i = 0; i < rows.length; i++) {
                // Dorong atribut 'data-id' ke array
                var id = rows[i].getAttribute('data-id');
                if (id) { // Pastikan baris 'data kosong' tidak ikut
                    itemIds.push(id);
                }
            }
            
            // 2. Kirim urutan baru ini ke server via AJAX (Fetch API)
            fetch('?page=saveSortOrder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ order: itemIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Urutan berhasil disimpan!');
                    // Opsional: kita bisa update nomor # (tapi tidak wajib)
                } else {
                    console.error('Gagal menyimpan urutan:', data.message);
                    alert('Gagal menyimpan urutan. Silakan refresh halaman.');
                }
            })
            .catch(error => {
                console.error('Error AJAX:', error);
                alert('Terjadi error. Silakan refresh halaman.');
            });
        }
    });
</script>

</body>
</html>
<?php
session_start();
require_once 'src/db.php';

class Note {
    public static function getAllNotes() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM notes ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function addNote($title, $date, $description) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO notes (title, date, description) VALUES (?, ?, ?)");
        return $stmt->execute([$title, $date, $description]);
    }
    public static function deleteNote($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public static function updateNote($id, $title, $date, $description) {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE notes SET title = ?, date = ?, description = ? WHERE id = ?");
        return $stmt->execute([$title, $date, $description, $id]);
    }
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            if (Note::addNote($_POST['title'], $_POST['date'], $_POST['description'])) {
                $_SESSION['message'] = "Catatan berhasil ditambahkan!";
            }
        } elseif ($_POST['action'] === 'update') {
            if (Note::updateNote($_POST['id'], $_POST['title'], $_POST['date'], $_POST['description'])) {
                $_SESSION['message'] = "Catatan berhasil diperbarui!";
            }
        }
        header("Location: index.php");
        exit();
    }
}

if (isset($_GET['delete'])) {
    if (Note::deleteNote($_GET['delete'])) {
        $_SESSION['message'] = "Catatan berhasil dihapus!";
    }
    header("Location: index.php");
    exit();
}

$notes = Note::getAllNotes();
$editNote = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editNote = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Harian Klasik</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Playfair+Display&display=swap" rel="stylesheet">

    <!-- Kustom Gaya Klasik -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="text-center">
    <h2>RIO NOTE</h2>
    <p>Aplikasi Catatan Harian Bergaya Klasik</p>
</header>

<div class="d-flex">
    <nav class="sidebar p-3">
        <h4 class="text-white">Menu</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="#" id="welcomeLink"><i class="fas fa-home"></i> Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#" id="notesLink"><i class="fas fa-sticky-note"></i> Catatan</a></li>
        </ul>
    </nav>

    <div class="container mt-4" style="flex-grow: 1;">
        <div id="welcomeSection">
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <h1 class="text-center">Selamat Datang di RIO NOTE</h1>
            <p class="text-center">Tempat untuk menyimpan catatan harian Anda dengan gaya klasik nan elegan.</p>
        </div>

        <div id="notesSection" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Catatan Anda</h2>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#noteModal">Tambah Catatan</button>
            </div>

            <div class="row" id="notes-container">
                <?php if (empty($notes)): ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Tidak ada catatan tersedia.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notes as $note): ?>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title"><?= htmlspecialchars($note['title']) ?></h5>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Tanggal: <?= htmlspecialchars($note['date']) ?></h6>
                                    <p class="card-text"><?= htmlspecialchars($note['description']) ?></p>
                                    <button class="btn btn-warning" data-toggle="modal" data-target="#noteModal" onclick="editNote(<?= $note['id'] ?>, '<?= htmlspecialchars($note['title']) ?>', '<?= htmlspecialchars($note['date']) ?>', '<?= htmlspecialchars($note['description']) ?>')">Edit</button>
                                    <a href="?delete=<?= $note['id'] ?>" class="btn btn-danger">Hapus</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noteModalLabel">Tambah Catatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="noteForm" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" id="noteId">
                    <div class="form-group">
                        <label for="title">Judul</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Tanggal (Opsional)</label>
                        <input type="date" class="form-control" id="date" name="date">
                    </div>
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Tambahkan Catatan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function editNote(id, title, date, description) {
    $('#noteModalLabel').text('Edit Catatan');
    $('#noteForm').attr('action', 'index.php');
    $('#noteId').val(id);
    $('#title').val(title);
    $('#date').val(date);
    $('#description').val(description);
    $('#noteForm button[type="submit"]').text('Perbarui Catatan');
    $('#noteForm input[name="action"]').val('update');
    $('#noteModal').modal('show');
}

$(document).ready(function() {
    $('#notesLink').click(function() {
        $('#welcomeSection').hide();
        $('#notesSection').show();
    });
    $('#welcomeLink').click(function() {
        $('#notesSection').hide();
        $('#welcomeSection').show();
    });
});
</script>
</body>
</html>

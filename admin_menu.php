<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/init.php';
initDatabase(getDB());
require_once __DIR__ . '/php/auth.php';
requireAdmin();

$pdo = getDB();
$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'add') {
        $nama  = trim($_POST['nama'] ?? '');
        $desc  = trim($_POST['deskripsi'] ?? '');
        $harga = $_POST['harga'] ?? '';
        $kat   = trim($_POST['kategori'] ?? 'Makanan');
        if ($nama === '' || $harga === '') { $error = 'Nama dan harga wajib diisi.'; }
        elseif (!is_numeric($harga) || $harga <= 0) { $error = 'Harga harus angka positif.'; }
        else {
            $pdo->prepare("INSERT INTO menu (nama, deskripsi, harga, kategori) VALUES (?,?,?,?)")->execute([$nama, $desc, (float)$harga, $kat]);
            $msg = 'Menu berhasil ditambahkan.';
        }
    } elseif ($act === 'edit') {
        $id    = (int)($_POST['id'] ?? 0);
        $nama  = trim($_POST['nama'] ?? '');
        $desc  = trim($_POST['deskripsi'] ?? '');
        $harga = $_POST['harga'] ?? '';
        $kat   = trim($_POST['kategori'] ?? 'Makanan');
        $avail = (int)($_POST['tersedia'] ?? 1);
        if ($nama === '' || $harga === '') { $error = 'Nama dan harga wajib diisi.'; }
        else {
            $pdo->prepare("UPDATE menu SET nama=?,deskripsi=?,harga=?,kategori=?,tersedia=? WHERE id=?")->execute([$nama,$desc,(float)$harga,$kat,$avail,$id]);
            $msg = 'Menu berhasil diperbarui.';
        }
    } elseif ($act === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE menu SET tersedia = 0 WHERE id = ?")->execute([$id]);
        $msg = 'Menu dinonaktifkan.';
    }
}

$menuList = $pdo->query("SELECT * FROM menu ORDER BY tersedia DESC, kategori, nama")->fetchAll();
$editMenu = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editMenu = $stmt->fetch();
}

$pageTitle = 'Kelola Menu — KantinKu';
include __DIR__ . '/php/header.php';
?>
<div class="container">
    <div class="page-header"><h1>Kelola Menu</h1></div>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-header"><?= $editMenu ? 'Edit Menu' : 'Tambah Menu' ?></div>
        <div class="card-body">
            <form method="post" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr <?= $editMenu ? '100px' : '' ?> auto;gap:.6rem;align-items:end;">
                <input type="hidden" name="action" value="<?= $editMenu ? 'edit' : 'add' ?>">
                <?php if ($editMenu): ?><input type="hidden" name="id" value="<?= $editMenu['id'] ?>"><?php endif; ?>
                <div class="form-group" style="margin:0;"><label>Nama Menu</label><input type="text" name="nama" value="<?= htmlspecialchars($editMenu['nama'] ?? '') ?>" required></div>
                <div class="form-group" style="margin:0;"><label>Deskripsi</label><input type="text" name="deskripsi" value="<?= htmlspecialchars($editMenu['deskripsi'] ?? '') ?>"></div>
                <div class="form-group" style="margin:0;"><label>Harga</label><input type="number" name="harga" value="<?= $editMenu['harga'] ?? '' ?>" min="1" required></div>
                <div class="form-group" style="margin:0;"><label>Kategori</label>
                    <select name="kategori">
                        <?php foreach (['Makanan','Minuman','Snack'] as $k): ?>
                        <option value="<?= $k ?>" <?= ($editMenu['kategori'] ?? 'Makanan') === $k ? 'selected' : '' ?>><?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($editMenu): ?>
                <div class="form-group" style="margin:0;"><label>Status</label>
                    <select name="tersedia"><option value="1" <?= $editMenu['tersedia'] ? 'selected' : '' ?>>Tersedia</option><option value="0" <?= !$editMenu['tersedia'] ? 'selected' : '' ?>>Nonaktif</option></select>
                </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-<?= $editMenu ? 'primary' : 'success' ?>"><?= $editMenu ? 'Update' : 'Tambah' ?></button>
            </form>
        </div>
    </div>

    <div class="card"><div class="card-body" style="padding:0;">
        <table>
            <thead><tr><th>Nama</th><th>Kategori</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($menuList as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['nama']) ?></td>
                <td><?= htmlspecialchars($m['kategori']) ?></td>
                <td>Rp <?= number_format($m['harga'],0,',','.') ?></td>
                <td><span class="badge <?= $m['tersedia'] ? 'badge-success' : 'badge-secondary' ?>"><?= $m['tersedia'] ? 'Tersedia' : 'Nonaktif' ?></span></td>
                <td style="display:flex;gap:.4rem;">
                    <a href="admin_menu.php?edit=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                    <?php if ($m['tersedia']): ?>
                    <form method="post" onsubmit="return confirm('Nonaktifkan menu ini?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Nonaktifkan</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</div>
</body>
</html>

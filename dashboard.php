<?php
session_start();
require 'koneksi.php';

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
$dirGambar = __DIR__ . '/gambar/';

function flash($tipe, $pesan) { $_SESSION['flash'] = [$tipe, $pesan]; }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function adaUrl($g) { return (bool)preg_match('#^https?://#i', $g); }

// Simpan gambar: file upload > URL > gambar lama
function prosesGambar($lama, $dir) {
    if (!empty($_FILES['gambar_file']['name']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['gambar_file'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) || !getimagesize($f['tmp_name']))
            throw new RuntimeException('File harus berupa gambar (jpg, png, webp, gif).');
        if ($f['size'] > 2 * 1024 * 1024) throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $nama = uniqid('produk_', true) . '.' . $ext;
        move_uploaded_file($f['tmp_name'], $dir . $nama);
        return $nama;
    }
    $url = trim($_POST['gambar_url'] ?? '');
    return $url !== '' ? $url : $lama;
}
function hapusFileLama($g, $dir) {
    if ($g && !adaUrl($g) && is_file($dir . basename($g))) @unlink($dir . basename($g));
}

// ---------- PROSES FORM (Create, Update, Delete) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($csrf, $_POST['csrf'] ?? '')) throw new RuntimeException('Sesi tidak valid, muat ulang halaman.');
        $aksi = $_POST['aksi'] ?? '';
        $id   = (int)($_POST['id'] ?? 0);

        if ($aksi === 'hapus') {
            $s = $pdo->prepare('SELECT gambar FROM products WHERE id = ?');
            $s->execute([$id]);
            $lama = $s->fetchColumn();
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            hapusFileLama($lama, $dirGambar);
            flash('success', 'Produk dihapus.');
        } elseif ($aksi === 'tambah' || $aksi === 'ubah') {
            $nama = trim($_POST['nama_produk'] ?? '');
            $harga = (int)($_POST['harga'] ?? -1);
            $stok  = (int)($_POST['stok'] ?? -1);
            if ($nama === '') throw new RuntimeException('Nama produk wajib diisi.');
            if ($harga < 0 || $stok < 0) throw new RuntimeException('Harga dan stok tidak boleh kosong atau negatif.');
            $desk = trim($_POST['deskripsi'] ?? '');
            $kat  = trim($_POST['kategori'] ?? '');

            if ($aksi === 'tambah') {
                $gambar = prosesGambar('', $dirGambar);
                $pdo->prepare('INSERT INTO products (nama_produk, deskripsi, gambar, kategori, harga, stok) VALUES (?,?,?,?,?,?)')
                    ->execute([$nama, $desk, $gambar ?: null, $kat ?: null, $harga, $stok]);
                flash('success', 'Produk ditambahkan.');
            } else {
                $s = $pdo->prepare('SELECT gambar FROM products WHERE id = ?');
                $s->execute([$id]);
                $lama   = (string)$s->fetchColumn();
                $gambar = prosesGambar($lama, $dirGambar);
                if ($gambar !== $lama) hapusFileLama($lama, $dirGambar);
                $pdo->prepare('UPDATE products SET nama_produk=?, deskripsi=?, gambar=?, kategori=?, harga=?, stok=? WHERE id=?')
                    ->execute([$nama, $desk, $gambar ?: null, $kat ?: null, $harga, $stok, $id]);
                flash('success', 'Perubahan disimpan.');
            }
        }
    } catch (PDOException $e) {
        // Kode 23000 = terikat foreign key (produk sudah ada di tabel orders)
        flash('danger', $e->getCode() === '23000'
            ? 'Produk tidak bisa dihapus karena sudah dipakai di pesanan.'
            : 'Kesalahan database: ' . $e->getMessage());
    } catch (RuntimeException $e) {
        flash('danger', $e->getMessage());
    }
    header('Location: dashboard.php' . (isset($_GET['q']) ? '?q=' . urlencode($_GET['q']) : ''));
    exit;
}

// ---------- AMBIL DATA (Read) ----------
$q = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare('SELECT * FROM products WHERE nama_produk LIKE ? OR kategori LIKE ? ORDER BY id DESC');
$stmt->execute(["%$q%", "%$q%"]);
$produk = $stmt->fetchAll();

$ringkas = $pdo->query('SELECT COUNT(*) jml, COALESCE(SUM(stok),0) stok, COALESCE(SUM(harga*stok),0) nilai,
                        SUM(stok = 0) habis FROM products')->fetch();
$daftarKategori = $pdo->query("SELECT DISTINCT kategori FROM products WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori")
                      ->fetchAll(PDO::FETCH_COLUMN);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Produk</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body { background: #f4f6f8; }
    .thumb { width: 52px; height: 52px; object-fit: cover; border-radius: .5rem; background: #e9ecef; }
    .thumb-kosong { width: 52px; height: 52px; border-radius: .5rem; background: #e9ecef; }
    .ringkas .angka { font-size: 1.6rem; font-weight: 700; }
    .table td { vertical-align: middle; }
    .cari-input { min-width: 280px; }
    .desk { max-width: 260px; }
  </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid px-4">
    <span class="navbar-brand fw-bold"><i class="bi bi-box-seam me-2"></i>Dashboard Produk</span>
   <a href="index.php" class="btn btn-outline-light btn-sm"><i class="bi bi-shop me-1"></i>Lihat toko</a>
  </div>
</nav>

<main class="container-fluid px-4 py-4">

  <?php if ($flash): ?>
    <div class="alert alert-<?= h($flash[0]) ?> alert-dismissible fade show" role="alert">
      <?= h($flash[1]) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
  <?php endif; ?>

  <!-- Ringkasan -->
  <div class="row g-3 mb-4 ringkas">
    <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm p-3"><div class="text-muted small">Total produk</div><div class="angka"><?= (int)$ringkas['jml'] ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm p-3"><div class="text-muted small">Total stok</div><div class="angka"><?= (int)$ringkas['stok'] ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm p-3"><div class="text-muted small">Stok habis</div><div class="angka text-danger"><?= (int)$ringkas['habis'] ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm p-3"><div class="text-muted small">Nilai inventori</div><div class="angka fs-4"><?= rupiah($ringkas['nilai']) ?></div></div></div>
  </div>

  <!-- Tabel produk -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
      <form class="d-flex gap-2" method="get">
        <input type="search" name="q" value="<?= h($q) ?>" class="form-control cari-input" placeholder="Cari nama atau kategori">
        <button class="btn btn-outline-secondary">Cari</button>
        <?php if ($q !== ''): ?><a href="dashboard.php" class="btn btn-link">Reset</a><?php endif; ?>
      </form>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="isiForm(null)">
        <i class="bi bi-plus-lg me-1"></i>Tambah produk
      </button>
    </div>

    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>ID</th><th>Gambar</th><th>Nama</th><th>Deskripsi</th><th>Kategori</th><th class="text-end">Harga</th><th class="text-end">Stok</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($produk as $p):
            $g = trim((string)$p['gambar']);
            $src = adaUrl($g) ? $g : ($g !== '' && is_file($dirGambar . basename($g)) ? 'gambar/' . rawurlencode(basename($g)) : ''); ?>
          <tr>
            <td><?= (int)$p['id'] ?></td>
            <td><?php if ($src): ?><img src="<?= h($src) ?>" class="thumb" alt="" referrerpolicy="no-referrer"><?php else: ?><div class="thumb-kosong"></div><?php endif; ?></td>
            <td class="fw-semibold"><?= h($p['nama_produk']) ?></td>
            <td class="desk text-muted small text-truncate"><?= h($p['deskripsi']) ?></td>
            <td><?= $p['kategori'] ? '<span class="badge text-bg-light border">' . h($p['kategori']) . '</span>' : '<span class="text-muted">-</span>' ?></td>
            <td class="text-end"><?= rupiah($p['harga']) ?></td>
            <td class="text-end"><span class="badge <?= $p['stok'] > 0 ? 'text-bg-success' : 'text-bg-danger' ?>"><?= (int)$p['stok'] ?></span></td>
            <td class="text-end text-nowrap">
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalForm"
                      onclick='isiForm(<?= h(json_encode($p, JSON_UNESCAPED_UNICODE)) ?>)'><i class="bi bi-pencil"></i> Ubah</button>
              <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapus"
                      onclick="siapkanHapus(<?= (int)$p['id'] ?>, <?= h(json_encode($p['nama_produk'])) ?>)"><i class="bi bi-trash"></i> Hapus</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$produk): ?>
          <tr><td colspan="8" class="text-center text-muted py-5">
            <?= $q !== '' ? 'Tidak ada produk yang cocok dengan pencarian.' : 'Belum ada produk. Klik "Tambah produk" untuk memulai.' ?>
          </td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Modal tambah / ubah -->
<div class="modal fade" id="modalForm" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="aksi" id="f_aksi" value="tambah">
      <input type="hidden" name="id" id="f_id">
      <div class="modal-header"><h5 class="modal-title" id="f_judul">Tambah produk</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-8"><label class="form-label">Nama produk</label><input name="nama_produk" id="f_nama" class="form-control" required maxlength="255"></div>
          <div class="col-md-4"><label class="form-label">Kategori</label>
            <input name="kategori" id="f_kategori" class="form-control" list="listKategori" maxlength="255">
            <datalist id="listKategori"><?php foreach ($daftarKategori as $k): ?><option value="<?= h($k) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="col-12"><label class="form-label">Deskripsi</label><textarea name="deskripsi" id="f_deskripsi" class="form-control" rows="3"></textarea></div>
          <div class="col-md-6"><label class="form-label">Harga (Rp)</label><input type="number" name="harga" id="f_harga" class="form-control" min="0" required></div>
          <div class="col-md-6"><label class="form-label">Stok</label><input type="number" name="stok" id="f_stok" class="form-control" min="0" required></div>
          <div class="col-md-6"><label class="form-label">Upload gambar</label><input type="file" name="gambar_file" class="form-control" accept="image/*"><div class="form-text">Maks. 2 MB (jpg, png, webp, gif).</div></div>
          <div class="col-md-6"><label class="form-label">Atau link gambar</label><input type="url" name="gambar_url" id="f_url" class="form-control" placeholder="https://..."><div class="form-text" id="f_info">Jika keduanya diisi, upload yang dipakai.</div></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
    </form>
  </div>
</div>

<!-- Modal konfirmasi hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="aksi" value="hapus">
      <input type="hidden" name="id" id="h_id">
      <div class="modal-header"><h5 class="modal-title">Hapus produk?</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body"><span id="h_nama" class="fw-semibold"></span> akan dihapus permanen dan tidak bisa dikembalikan.</div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Hapus</button></div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function isiForm(p) {
  const ubah = p !== null;
  document.getElementById('f_judul').textContent = ubah ? 'Ubah produk' : 'Tambah produk';
  document.getElementById('f_aksi').value = ubah ? 'ubah' : 'tambah';
  document.getElementById('f_id').value = ubah ? p.id : '';
  document.getElementById('f_nama').value = ubah ? p.nama_produk : '';
  document.getElementById('f_kategori').value = ubah ? (p.kategori || '') : '';
  document.getElementById('f_deskripsi').value = ubah ? (p.deskripsi || '') : '';
  document.getElementById('f_harga').value = ubah ? p.harga : '';
  document.getElementById('f_stok').value = ubah ? p.stok : '';
  const g = ubah ? (p.gambar || '') : '';
  document.getElementById('f_url').value = /^https?:\/\//i.test(g) ? g : '';
  document.getElementById('f_info').textContent = ubah && g
    ? 'Kosongkan upload dan link untuk mempertahankan gambar saat ini.'
    : 'Jika keduanya diisi, upload yang dipakai.';
  document.querySelector('#modalForm input[type=file]').value = '';
}
function siapkanHapus(id, nama) {
  document.getElementById('h_id').value = id;
  document.getElementById('h_nama').textContent = nama;
}
</script>
</body>
</html>
<?php
require 'koneksi.php';

$perHalaman = 8;
$halaman    = max(1, (int)($_GET['hal'] ?? 1));
$cari       = trim($_GET['q'] ?? '');

$kategori = trim($_GET['kategori'] ?? '');

// Filter pencarian dan kategori
$where  = [];
$params = [];
if ($cari !== '') {
    $where[]  = 'nama_produk LIKE ?';
    $params[] = "%$cari%";
}
if ($kategori !== '') {
    $where[]  = 'kategori = ?';
    $params[] = $kategori;
}
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Daftar kategori untuk tombol filter
$daftarKategori = $pdo->query(
    "SELECT DISTINCT kategori FROM products
     WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori"
)->fetchAll(PDO::FETCH_COLUMN);

// Hitung total produk untuk pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products $sqlWhere");
$stmt->execute($params);
$total    = (int)$stmt->fetchColumn();
$totalHal = max(1, (int)ceil($total / $perHalaman));
$halaman  = min($halaman, $totalHal);
$offset   = ($halaman - 1) * $perHalaman;

// Ambil produk
$stmt = $pdo->prepare("SELECT * FROM products $sqlWhere ORDER BY id DESC LIMIT $perHalaman OFFSET $offset");
$stmt->execute($params);
$produk = $stmt->fetchAll();

function rupiah($angka) { return 'Rp ' . number_format($angka, 0, ',', '.'); }
function urlHal($hal)   { return '?' . http_build_query(array_merge($_GET, ['hal' => $hal])); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Toko Online</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    .produk-gambar, .produk-kosong { height: 200px; }
    .produk-gambar { object-fit: cover; }
    .card { transition: box-shadow .2s; }
    .card:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.12); }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-bag-heart me-2"></i>Ariq Shop wkwk</a>
    <form class="d-flex" method="get" role="search">
      <?php if ($kategori !== ''): ?>
  <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
<?php endif; ?>
      <input class="form-control me-2" type="search" name="q" placeholder="Cari produk"
             value="<?= htmlspecialchars($cari) ?>">
      <button class="btn btn-outline-light" type="submit">Cari</button>
    </form>
  </div>
</nav>

<main class="container py-4">
  <form method="get" class="d-flex gap-2 mb-4" style="max-width: 420px;">
  <?php if ($cari !== ''): ?>
    <input type="hidden" name="q" value="<?= htmlspecialchars($cari) ?>">
  <?php endif; ?>
  <select name="kategori" class="form-select">
    <option value="">Semua kategori</option>
    <?php foreach ($daftarKategori as $k): ?>
      <option value="<?= htmlspecialchars($k) ?>" <?= $kategori === $k ? 'selected' : '' ?>>
        <?= htmlspecialchars($k) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-primary">Cari</button>
</form>
  <p class="text-muted"><?= $total ?> produk ditemukan</p>

  <?php if (!$produk): ?>
    <div class="alert alert-warning">Tidak ada produk yang cocok. Coba kata kunci lain.</div>
  <?php endif; ?>

  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
    <?php foreach ($produk as $p): ?>
      <?php
        // Gambar bisa berupa link (https://...) atau nama file di folder gambar/
        $gambar = trim($p['gambar'] ?? '');
        $adaGambar = false;
        $srcGambar = '';
        if (preg_match('#^https?://#i', $gambar)) {
            $adaGambar = true;
            $srcGambar = $gambar;
        } elseif ($gambar !== '' && file_exists(__DIR__ . '/gambar/' . $gambar)) {
            $adaGambar = true;
            $srcGambar = 'gambar/' . $gambar;
        }
      ?>
      <div class="col">
        <div class="card h-100 border-0 shadow-sm">
          <?php if ($adaGambar): ?>
            <img src="<?= htmlspecialchars($srcGambar) ?>" class="card-img-top produk-gambar"
                 alt="<?= htmlspecialchars($p['nama_produk']) ?>" referrerpolicy="no-referrer">
          <?php else: ?>
            <div class="produk-kosong bg-secondary-subtle d-flex align-items-center justify-content-center">
              <i class="bi bi-image fs-1 text-secondary"></i>
            </div>
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fs-6"><?= htmlspecialchars($p['nama_produk']) ?></h5>
            <p class="card-text small text-muted flex-grow-1"><?= htmlspecialchars($p['deskripsi'] ?? '') ?></p>
            <div class="fw-bold fs-5 mb-1"><?= rupiah($p['harga']) ?></div>
            <div class="small mb-3 <?= $p['stok'] > 0 ? 'text-success' : 'text-danger' ?>">
              <?= $p['stok'] > 0 ? 'Stok: ' . (int)$p['stok'] : 'Stok habis' ?>
            </div>
            <button class="btn btn-primary" <?= $p['stok'] > 0 ? '' : 'disabled' ?>>
              <i class="bi bi-cart-plus me-1"></i>Tambah ke keranjang
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($totalHal > 1): ?>
    <nav class="mt-5" aria-label="Halaman produk">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= urlHal($halaman - 1) ?>">Sebelumnya</a>
        </li>
        <?php for ($i = 1; $i <= $totalHal; $i++): ?>
          <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
            <a class="page-link" href="<?= urlHal($i) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $halaman >= $totalHal ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= urlHal($halaman + 1) ?>">Berikutnya</a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>
</main>

<footer class="text-center text-muted py-4 border-top">&copy; <?= date('Y') ?> Toko Online</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
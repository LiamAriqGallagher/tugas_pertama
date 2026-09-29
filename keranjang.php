<?php
session_start();
require 'koneksi.php';

$sid = session_id(); // pengenal keranjang milik pengunjung ini

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function adaUrl($g) { return (bool)preg_match('#^https?://#i', $g); }
function flash($tipe, $pesan) { $_SESSION['flash'] = [$tipe, $pesan]; }

function kembali() {
    $tujuan = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    $tujuan = (parse_url($tujuan, PHP_URL_PATH)) ? basename(parse_url($tujuan, PHP_URL_PATH)) : 'index.php';
    if (!in_array($tujuan, ['index.php', 'keranjang.php'], true)) $tujuan = 'index.php';
    header('Location: ' . $tujuan);
    exit;
}

// ---------- Proses aksi keranjang (baca/tulis tabel `keranjang`) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id'] ?? 0); // untuk 'tambah': id produk. untuk 'update'/'hapus': id baris keranjang

    if ($aksi === 'tambah' && $id > 0) {
        $s = $pdo->prepare('SELECT id, nama_produk, stok FROM products WHERE id = ?');
        $s->execute([$id]);
        $p = $s->fetch();
        if (!$p) {
            flash('danger', 'Produk tidak ditemukan.');
        } elseif ((int)$p['stok'] <= 0) {
            flash('danger', 'Stok "' . $p['nama_produk'] . '" habis.');
        } else {
            $qtyDiminta = max(1, (int)($_POST['qty'] ?? 1));

            // Cek qty yang sudah ada di keranjang untuk produk + sesi ini
            $s2 = $pdo->prepare('SELECT qty FROM keranjang WHERE product_id = ? AND session_id = ?');
            $s2->execute([$id, $sid]);
            $qtySkrg = (int)($s2->fetchColumn() ?: 0);
            $qtyBaru = min((int)$p['stok'], $qtySkrg + $qtyDiminta);

            // Simpan: tambah baris baru, atau tambah qty kalau produk ini sudah ada di keranjang
            $pdo->prepare(
                'INSERT INTO keranjang (product_id, session_id, qty) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE qty = ?'
            )->execute([$id, $sid, $qtyBaru, $qtyBaru]);

            flash('success', $p['nama_produk'] . ' ditambahkan ke keranjang.');
        }
    } elseif ($aksi === 'update' && $id > 0) {
        $qty = (int)($_POST['qty'] ?? 0);
        if ($qty <= 0) {
            $pdo->prepare('DELETE FROM keranjang WHERE id = ? AND session_id = ?')->execute([$id, $sid]);
        } else {
            $s = $pdo->prepare('SELECT product_id FROM keranjang WHERE id = ? AND session_id = ?');
            $s->execute([$id, $sid]);
            $pid = $s->fetchColumn();
            if ($pid) {
                $stok = (int)$pdo->query('SELECT stok FROM products WHERE id = ' . (int)$pid)->fetchColumn();
                $qty  = min($qty, max($stok, 0));
                $pdo->prepare('UPDATE keranjang SET qty = ? WHERE id = ? AND session_id = ?')->execute([$qty, $id, $sid]);
            }
        }
    } elseif ($aksi === 'hapus' && $id > 0) {
        $pdo->prepare('DELETE FROM keranjang WHERE id = ? AND session_id = ?')->execute([$id, $sid]);
        flash('success', 'Produk dihapus dari keranjang.');
    } elseif ($aksi === 'kosongkan') {
        $pdo->prepare('DELETE FROM keranjang WHERE session_id = ?')->execute([$sid]);
        flash('success', 'Keranjang dikosongkan.');
    }
    kembali();
}

// ---------- Ambil isi keranjang milik sesi ini, gabung dengan data produk ----------
$stmt = $pdo->prepare(
    'SELECT k.id AS keranjang_id, k.qty, p.*
     FROM keranjang k JOIN products p ON p.id = k.product_id
     WHERE k.session_id = ? ORDER BY k.id DESC'
);
$stmt->execute([$sid]);
$baris = $stmt->fetchAll();

$item = [];
$total = 0;
foreach ($baris as $b) {
    $qty = min((int)$b['qty'], (int)$b['stok']);
    if ($qty <= 0) {
        $pdo->prepare('DELETE FROM keranjang WHERE id = ?')->execute([$b['keranjang_id']]);
        continue;
    }
    $subtotal = $qty * (int)$b['harga'];
    $total   += $subtotal;
    $item[]   = ['b' => $b, 'qty' => $qty, 'subtotal' => $subtotal];
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Keranjang Belanja</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    .thumb { width: 64px; height: 64px; object-fit: cover; border-radius: .5rem; background: #e9ecef; }
    .qty-input { width: 70px; }
    .js-qty::-webkit-inner-spin-button,
.js-qty::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.js-qty { -moz-appearance: textfield; appearance: textfield; }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-bag-heart me-2"></i>Ariq Shop wkwk</a>
    <a href="index.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left me-1"></i>Lanjutkan belanja</a>
  </div>
</nav>

<main class="container py-4">
  <h4 class="mb-4"><i class="bi bi-cart3 me-2"></i>Keranjang Belanja</h4>

  <?php if ($flash): ?>
    <div class="alert alert-<?= h($flash[0]) ?> alert-dismissible fade show" role="alert">
      <?= h($flash[1]) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!$item): ?>
    <div class="card border-0 shadow-sm text-center py-5">
      <i class="bi bi-cart-x fs-1 text-secondary mb-2"></i>
      <p class="text-muted mb-3">Keranjang Anda masih kosong.</p>
      <a href="index.php" class="btn btn-primary mx-auto" style="width:fit-content">Mulai belanja</a>
    </div>
  <?php else: ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr><th>Produk</th><th class="text-end">Harga</th><th class="text-center">Jumlah</th><th class="text-end">Subtotal</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach ($item as $it):
              $b = $it['b'];
              $g = trim((string)$b['gambar']);
              $src = adaUrl($g) ? $g : ($g !== '' && is_file(__DIR__ . '/gambar/' . basename($g)) ? 'gambar/' . rawurlencode(basename($g)) : '');
          ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <?php if ($src): ?><img src="<?= h($src) ?>" class="thumb" alt="" referrerpolicy="no-referrer">
                  <?php else: ?><div class="thumb d-flex align-items-center justify-content-center"><i class="bi bi-image text-secondary"></i></div><?php endif; ?>
                  <div>
                    <div class="fw-semibold"><?= h($b['nama_produk']) ?></div>
                    <div class="small text-muted">Stok tersedia: <?= (int)$b['stok'] ?></div>
                  </div>
                </div>
              </td>
              <td class="text-end"><?= rupiah($b['harga']) ?></td>
              <td>
                <form method="post" class="d-flex justify-content-center align-items-center gap-1 js-form-qty">
                  <input type="hidden" name="aksi" value="update">
                  <input type="hidden" name="id" value="<?= (int)$b['keranjang_id'] ?>">
                  <button type="button" class="btn btn-sm btn-outline-secondary js-kurang" title="Kurangi">
                    <i class="bi bi-dash"></i>
                  </button>
                  <input type="number" name="qty" value="<?= (int)$it['qty'] ?>" min="1" max="<?= (int)$b['stok'] ?>"
                         class="form-control form-control-sm qty-input text-center js-qty"
                         data-harga="<?= (int)$b['harga'] ?>">
                  <button type="button" class="btn btn-sm btn-outline-secondary js-tambah" title="Tambah">
                    <i class="bi bi-plus"></i>
                  </button>
                </form>
              </td>
              <td class="text-end fw-semibold js-subtotal"><?= rupiah($it['subtotal']) ?></td>
              <td class="text-end">
                <form method="post">
                  <input type="hidden" name="aksi" value="hapus">
                  <input type="hidden" name="id" value="<?= (int)$b['keranjang_id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <form method="post" onsubmit="return confirm('Kosongkan seluruh keranjang?');">
        <input type="hidden" name="aksi" value="kosongkan">
        <button class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Kosongkan keranjang</button>
      </form>
      <div class="card border-0 shadow-sm p-3" style="min-width: 280px;">
        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Total</span><span class="fw-bold fs-5" id="js-total"><?= rupiah($total) ?></span></div>
        <button class="btn btn-primary" disabled title="Checkout belum tersedia, perlu login terlebih dahulu">
          <i class="bi bi-credit-card me-1"></i>Checkout
        </button>
        <div class="form-text mt-1">Checkout memerlukan akun. Fitur login belum tersedia.</div>
      </div>
    </div>
  <?php endif; ?>
</main>
<script>
function rupiahJS(n) {
  return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}
function perbaruiHarga(input) {
  const baris = input.closest('tr');
  const harga = Number(input.dataset.harga);
  const qty = Math.max(0, Number(input.value) || 0);
  baris.querySelector('.js-subtotal').textContent = rupiahJS(harga * qty);

  let total = 0;
  document.querySelectorAll('.js-qty').forEach(i => {
    total += Number(i.dataset.harga) * (Math.max(0, Number(i.value) || 0));
  });
  document.getElementById('js-total').textContent = rupiahJS(total);
}

document.querySelectorAll('.js-kurang').forEach(tombol => {
  tombol.addEventListener('click', () => {
    const form  = tombol.closest('form');
    const input = form.querySelector('.js-qty');
    const min   = Number(input.min) || 1;
    input.value = Math.max(min, Number(input.value) - 1);
    perbaruiHarga(input);
    form.submit();
  });
});

document.querySelectorAll('.js-tambah').forEach(tombol => {
  tombol.addEventListener('click', () => {
    const form  = tombol.closest('form');
    const input = form.querySelector('.js-qty');
    const max   = Number(input.max) || 9999;
    input.value = Math.min(max, Number(input.value) + 1);
    perbaruiHarga(input);
    form.submit();
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
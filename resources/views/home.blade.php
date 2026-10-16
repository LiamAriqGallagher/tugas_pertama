@extends('layout')

@section('title', 'TokoKita - Belanja Online Mudah & Terpercaya')

@section('content')

  <!-- Hero -->
  <header class="bg-primary text-white py-5">
    <div class="container py-lg-4">
      <div class="row align-items-center g-4">
        <div class="col-lg-6">
          <span class="badge text-bg-warning mb-3">Promo Akhir Tahun</span>
          <h1 class="display-5 fw-bold">Belanja Lebih Hemat, Pilihan Lebih Lengkap</h1>
          <p class="lead mb-4">Temukan ribuan produk berkualitas dengan harga terbaik. Pengiriman cepat ke seluruh Indonesia.</p>
          <a href="#produk" class="btn btn-light btn-lg me-2">Belanja Sekarang</a>
          <a href="#promo" class="btn btn-outline-light btn-lg">Lihat Promo</a>
        </div>
        <div class="col-lg-6">
          <img src="https://picsum.photos/seed/hero-shop/800/500" class="img-fluid rounded-4 shadow" alt="Banner promo TokoKita">
        </div>
      </div>
    </div>
  </header>

  <!-- Keunggulan -->
  <section class="py-4 bg-light border-bottom">
    <div class="container">
      <div class="row text-center g-3">
        <div class="col-6 col-md-3">
          <i class="bi bi-truck fs-2 text-primary"></i>
          <div class="fw-semibold">Pengiriman Cepat</div>
          <small class="text-muted">Ke seluruh Indonesia</small>
        </div>
        <div class="col-6 col-md-3">
          <i class="bi bi-shield-check fs-2 text-primary"></i>
          <div class="fw-semibold">Pembayaran Aman</div>
          <small class="text-muted">Transaksi terlindungi</small>
        </div>
        <div class="col-6 col-md-3">
          <i class="bi bi-arrow-repeat fs-2 text-primary"></i>
          <div class="fw-semibold">Retur 7 Hari</div>
          <small class="text-muted">Mudah &amp; tanpa ribet</small>
        </div>
        <div class="col-6 col-md-3">
          <i class="bi bi-headset fs-2 text-primary"></i>
          <div class="fw-semibold">Layanan 24/7</div>
          <small class="text-muted">Siap membantu Anda</small>
        </div>
      </div>
    </div>
  </section>

  <!-- Kategori -->
  <section id="kategori" class="py-5">
    <div class="container">
      <h2 class="h3 fw-bold mb-4">Belanja Berdasarkan Kategori</h2>
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
        <div class="col"><a href="#produk" class="card text-center text-decoration-none h-100 py-3 border-0 shadow-sm"><i class="bi bi-phone fs-1 text-primary"></i><div class="mt-2 text-dark fw-semibold">Elektronik</div></a></div>
        <div class="col"><a href="#produk" class="card text-center text-decoration-none h-100 py-3 border-0 shadow-sm"><i class="bi bi-bag fs-1 text-primary"></i><div class="mt-2 text-dark fw-semibold">Fashion</div></a></div>
        <div class="col"><a href="#produk" class="card text-center text-decoration-none h-100 py-3 border-0 shadow-sm"><i class="bi bi-house fs-1 text-primary"></i><div class="mt-2 text-dark fw-semibold">Rumah Tangga</div></a></div>
        <div class="col"><a href="#produk" class="card text-center text-decoration-none h-100 py-3 border-0 shadow-sm"><i class="bi bi-heart-pulse fs-1 text-primary"></i><div class="mt-2 text-dark fw-semibold">Kesehatan</div></a></div>
        <div class="col"><a href="#produk" class="card text-center text-decoration-none h-100 py-3 border-0 shadow-sm"><i class="bi bi-controller fs-1 text-primary"></i><div class="mt-2 text-dark fw-semibold">Hobi &amp; Game</div></a></div>
        <div class="col"><a href="#produk" class="card text-center text-decoration-none h-100 py-3 border-0 shadow-sm"><i class="bi bi-cup-hot fs-1 text-primary"></i><div class="mt-2 text-dark fw-semibold">Makanan</div></a></div>
      </div>
    </div>
  </section>

  <!-- Produk -->
  <section id="produk" class="pb-5">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold mb-0">Produk Unggulan</h2>
        <select id="sortSelect" class="form-select w-auto" aria-label="Urutkan produk">
          <option value="default">Urutkan</option>
          <option value="termurah">Harga Termurah</option>
          <option value="termahal">Harga Termahal</option>
        </select>
      </div>

      <div id="productGrid" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-lg-4">
        <!-- Produk dirender oleh JavaScript di bawah -->
      </div>

      <div class="text-center mt-5">
        <a href="#" class="btn btn-outline-primary btn-lg">Lihat Semua Produk</a>
      </div>
    </div>
  </section>

  <!-- Banner Promo -->
  <section id="promo" class="py-5 bg-primary-subtle">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-md-8">
          <h2 class="fw-bold">Diskon Hingga 50% untuk Pengguna Baru!</h2>
          <p class="mb-0">Daftar sekarang dan dapatkan voucher belanja Rp50.000 untuk pembelian pertama Anda.</p>
        </div>
        <div class="col-md-4 text-md-end">
          <a href="#" class="btn btn-primary btn-lg">Daftar Gratis</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Newsletter -->
  <section class="py-5">
    <div class="container text-center">
      <h2 class="h4 fw-bold">Dapatkan Info Promo Terbaru</h2>
      <p class="text-muted">Berlangganan newsletter kami dan jangan lewatkan penawaran spesial.</p>
      <form class="row justify-content-center g-2" onsubmit="event.preventDefault(); alert('Terima kasih sudah berlangganan!'); this.reset();">
        <div class="col-12 col-md-5">
          <input type="email" class="form-control form-control-lg" placeholder="Alamat email Anda" required>
        </div>
        <div class="col-12 col-md-auto">
          <button class="btn btn-primary btn-lg w-100" type="submit">Berlangganan</button>
        </div>
      </form>
    </div>
  </section>

@endsection

@push('scripts')
<script>
  // Data produk contoh - ganti dengan data dari database/API Anda
  const products = [
    { id: 1, nama: "Headphone Nirkabel Pro",   harga: 450000,  coret: 599000,  rating: 4.8, label: "Diskon" },
    { id: 2, nama: "Jam Tangan Pintar S2",     harga: 899000,  coret: null,    rating: 4.6, label: "Baru" },
    { id: 3, nama: "Sepatu Lari Ultralight",   harga: 520000,  coret: 650000,  rating: 4.7, label: "Diskon" },
    { id: 4, nama: "Tas Ransel Anti Air",      harga: 275000,  coret: null,    rating: 4.5, label: "" },
    { id: 5, nama: "Kamera Aksi 4K",           harga: 1250000, coret: 1499000, rating: 4.9, label: "Terlaris" },
    { id: 6, nama: "Speaker Bluetooth Mini",   harga: 199000,  coret: null,    rating: 4.4, label: "" },
    { id: 7, nama: "Kemeja Linen Premium",     harga: 189000,  coret: 249000,  rating: 4.3, label: "Diskon" },
    { id: 8, nama: "Lampu Meja LED Minimalis", harga: 149000,  coret: null,    rating: 4.6, label: "Baru" }
  ];

  const rupiah = n => "Rp" + n.toLocaleString("id-ID");
  const grid = document.getElementById("productGrid");
  const searchInput = document.getElementById("searchInput");
  const sortSelect = document.getElementById("sortSelect");

  function bintang(r) {
    const penuh = Math.round(r);
    return "★".repeat(penuh) + "☆".repeat(5 - penuh);
  }

  function render() {
    const q = searchInput.value.trim().toLowerCase();
    let list = products.filter(p => p.nama.toLowerCase().includes(q));

    if (sortSelect.value === "termurah") list.sort((a, b) => a.harga - b.harga);
    if (sortSelect.value === "termahal") list.sort((a, b) => b.harga - a.harga);

    if (list.length === 0) {
      grid.innerHTML = '<div class="col-12 text-center text-muted py-5">Produk tidak ditemukan.</div>';
      return;
    }

    grid.innerHTML = list.map(p => `
      <div class="col">
        <div class="card h-100 border-0 shadow-sm">
          <div class="position-relative">
            <div class="ratio ratio-1x1">
              <img src="https://picsum.photos/seed/produk${p.id}/400/400" class="card-img-top object-fit-cover" alt="${p.nama}" loading="lazy">
            </div>
            ${p.label ? `<span class="badge text-bg-danger position-absolute top-0 start-0 m-2">${p.label}</span>` : ""}
          </div>
          <div class="card-body d-flex flex-column">
            <h3 class="h6 card-title">${p.nama}</h3>
            <div class="text-warning small mb-2">${bintang(p.rating)} <span class="text-muted">${p.rating}</span></div>
            <div class="mt-auto">
              <div class="fw-bold text-primary">${rupiah(p.harga)}</div>
              ${p.coret ? `<small class="text-muted text-decoration-line-through">${rupiah(p.coret)}</small>` : '<small>&nbsp;</small>'}
              <button class="btn btn-primary btn-sm w-100 mt-2 btn-add" data-id="${p.id}">
                <i class="bi bi-cart-plus me-1"></i>Tambah ke Keranjang
              </button>
            </div>
          </div>
        </div>
      </div>
    `).join("");
  }

  grid.addEventListener("click", e => {
    const btn = e.target.closest(".btn-add");
    if (!btn) return;

    const p = products.find(x => x.id === Number(btn.dataset.id));
    const cart = loadCart();
    const item = cart.find(i => i.id === p.id);
    if (item) item.qty++; else cart.push({ id: p.id, nama: p.nama, harga: p.harga, qty: 1 });
    saveCart(cart);
    updateBadge();

    btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Ditambahkan';
    setTimeout(() => btn.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Tambah ke Keranjang', 1200);
  });

  searchInput.addEventListener("input", render);
  sortSelect.addEventListener("change", render);
  render();
</script>
@endpush
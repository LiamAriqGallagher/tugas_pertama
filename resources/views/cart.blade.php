@extends('layout')

@section('title', 'Keranjang Belanja - TokoKita')

@section('content')
<main class="bg-light">
  <div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Keranjang</li>
      </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Keranjang Belanja</h1>

    <!-- Keranjang kosong -->
    <div id="emptyState" class="card border-0 shadow-sm text-center py-5 d-none">
      <div class="card-body">
        <i class="bi bi-cart-x display-1 text-muted"></i>
        <h2 class="h5 mt-3">Keranjang Anda masih kosong</h2>
        <p class="text-muted">Yuk, temukan produk favorit Anda.</p>
        <a href="{{ route('home') }}#produk" class="btn btn-primary">Mulai Belanja</a>
      </div>
    </div>

    <!-- Isi keranjang -->
    <div id="cartContent" class="row g-4 d-none">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <span class="fw-semibold"><span id="itemTotal">0</span> item</span>
            <button id="clearBtn" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-trash me-1"></i>Kosongkan
            </button>
          </div>
          <ul id="cartList" class="list-group list-group-flush"></ul>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h5 fw-bold mb-3">Ringkasan Pesanan</h2>

            <div class="input-group mb-1">
              <input id="voucherInput" type="text" class="form-control" placeholder="Kode voucher">
              <button id="voucherBtn" class="btn btn-outline-primary" type="button">Pakai</button>
            </div>
            <div id="voucherMsg" class="small mb-3"></div>

            <dl class="row mb-0">
              <dt class="col-6 fw-normal">Subtotal</dt>
              <dd class="col-6 text-end" id="subtotal">Rp0</dd>

              <dt class="col-6 fw-normal">Diskon</dt>
              <dd class="col-6 text-end text-success" id="discount">-Rp0</dd>

              <dt class="col-6 fw-normal">Ongkos kirim</dt>
              <dd class="col-6 text-end" id="shipping">Rp0</dd>
            </dl>
            <div id="shippingHint" class="small text-muted mb-2"></div>

            <hr>
            <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
              <span>Total</span>
              <span class="text-primary" id="grandTotal">Rp0</span>
            </div>

            <button id="checkoutBtn" class="btn btn-primary btn-lg w-100">
              <i class="bi bi-lock me-1"></i>Lanjut ke Pembayaran
            </button>
            <p class="small text-muted text-center mt-3 mb-0">
              <i class="bi bi-shield-check me-1"></i>Pembayaran aman &amp; terenkripsi
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script>
  const VOUCHER_KEY = "tokokita_voucher";
  const GRATIS_ONGKIR_MIN = 300000;
  const ONGKIR = 20000;
  const VOUCHERS = { HEMAT10: 0.10 }; // 10% diskon

  const rupiah = n => "Rp" + Math.round(n).toLocaleString("id-ID");
  const $ = id => document.getElementById(id);
  const escapeHtml = s => s.replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));

  let cart = loadCart();
  let voucher = "";
  try { voucher = JSON.parse(localStorage.getItem(VOUCHER_KEY)) || ""; } catch (e) {}

  function render() {
    updateBadge();
    const totalQty = cart.reduce((s, i) => s + i.qty, 0);
    $("itemTotal").textContent = totalQty;

    const empty = cart.length === 0;
    $("emptyState").classList.toggle("d-none", !empty);
    $("cartContent").classList.toggle("d-none", empty);
    if (empty) return;

    $("cartList").innerHTML = cart.map(i => `
      <li class="list-group-item py-3">
        <div class="row g-3 align-items-center">
          <div class="col-auto">
            <img src="https://picsum.photos/seed/produk${i.id}/160/160" width="80" height="80" class="rounded object-fit-cover" alt="${escapeHtml(i.nama)}">
          </div>
          <div class="col">
            <div class="fw-semibold">${escapeHtml(i.nama)}</div>
            <div class="text-muted small">${rupiah(i.harga)} / item</div>
            <button class="btn btn-link btn-sm text-danger p-0 mt-1" data-act="remove" data-id="${i.id}">
              <i class="bi bi-trash"></i> Hapus
            </button>
          </div>
          <div class="col-12 col-sm-auto d-flex align-items-center justify-content-between gap-3">
            <div class="input-group input-group-sm w-auto">
              <button class="btn btn-outline-secondary" data-act="dec" data-id="${i.id}" aria-label="Kurangi">&minus;</button>
              <span class="input-group-text bg-white">${i.qty}</span>
              <button class="btn btn-outline-secondary" data-act="inc" data-id="${i.id}" aria-label="Tambah">+</button>
            </div>
            <div class="fw-bold text-end">${rupiah(i.harga * i.qty)}</div>
          </div>
        </div>
      </li>
    `).join("");

    const subtotal = cart.reduce((s, i) => s + i.harga * i.qty, 0);
    const rate = VOUCHERS[voucher] || 0;
    const diskon = subtotal * rate;
    const afterDiscount = subtotal - diskon;
    const ongkir = afterDiscount >= GRATIS_ONGKIR_MIN ? 0 : ONGKIR;

    $("subtotal").textContent = rupiah(subtotal);
    $("discount").textContent = "-" + rupiah(diskon);
    $("shipping").textContent = ongkir === 0 ? "Gratis" : rupiah(ongkir);
    $("shippingHint").textContent = ongkir === 0
      ? "Anda mendapat gratis ongkir."
      : `Belanja ${rupiah(GRATIS_ONGKIR_MIN - afterDiscount)} lagi untuk gratis ongkir.`;
    $("grandTotal").textContent = rupiah(afterDiscount + ongkir);

    if (voucher && rate) {
      $("voucherMsg").className = "small mb-3 text-success";
      $("voucherMsg").textContent = `Voucher ${voucher} diterapkan (${rate * 100}% diskon).`;
      $("voucherInput").value = voucher;
    }
  }

  function persist() { saveCart(cart); render(); }

  $("cartList").addEventListener("click", e => {
    const btn = e.target.closest("[data-act]");
    if (!btn) return;
    const id = Number(btn.dataset.id);
    const item = cart.find(i => i.id === id);
    if (!item) return;

    if (btn.dataset.act === "inc") item.qty++;
    if (btn.dataset.act === "dec") item.qty = Math.max(1, item.qty - 1);
    if (btn.dataset.act === "remove") cart = cart.filter(i => i.id !== id);
    persist();
  });

  $("clearBtn").addEventListener("click", () => {
    if (confirm("Kosongkan semua item di keranjang?")) {
      cart = [];
      persist();
    }
  });

  $("voucherBtn").addEventListener("click", () => {
    const code = $("voucherInput").value.trim().toUpperCase();
    if (VOUCHERS[code]) {
      voucher = code;
      try { localStorage.setItem(VOUCHER_KEY, JSON.stringify(voucher)); } catch (e) {}
      render();
    } else {
      voucher = "";
      try { localStorage.setItem(VOUCHER_KEY, JSON.stringify("")); } catch (e) {}
      render();
      $("voucherMsg").className = "small mb-3 text-danger";
      $("voucherMsg").textContent = "Kode voucher tidak valid.";
    }
  });

  $("checkoutBtn").addEventListener("click", () => {
    alert("Fitur pembayaran belum dihubungkan. Sambungkan ke halaman checkout atau payment gateway Anda.");
  });

  render();
</script>
@endpush
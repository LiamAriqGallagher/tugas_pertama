<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'TokoKita')</title>
  <meta name="description" content="TokoKita menyediakan berbagai produk pilihan dengan harga terbaik.">

  <!-- Bootstrap CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>

  <!-- Top bar -->
  <div class="bg-dark text-white small py-2 text-center">
    Gratis ongkir untuk pembelian di atas Rp300.000 &middot; Gunakan kode <strong>HEMAT10</strong>
  </div>

  @include('navbar')

  @yield('content')

  @include('footer')

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- JS bersama (dipakai semua halaman): keranjang di localStorage + badge -->
  <script>
    const CART_KEY = "tokokita_cart";

    function loadCart() {
      try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; } catch (e) { return []; }
    }
    function saveCart(c) {
      try { localStorage.setItem(CART_KEY, JSON.stringify(c)); } catch (e) {}
    }
    function updateBadge() {
      const el = document.getElementById("cartCount");
      if (el) el.textContent = loadCart().reduce((s, i) => s + i.qty, 0);
    }
    updateBadge();
  </script>

  <!-- JS khusus per halaman -->
  @stack('scripts')
</body>
</html>
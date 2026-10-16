<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="{{ route('home') }}">
      <i class="bi bi-bag-heart-fill me-1"></i>TokoKita
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
            aria-controls="navMenu" aria-expanded="false" aria-label="Buka menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#kategori">Kategori</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#produk">Produk</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#promo">Promo</a></li>
        <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
      </ul>

      <form class="d-flex me-lg-3 mb-2 mb-lg-0" role="search" onsubmit="return false;">
        <div class="input-group">
          <input id="searchInput" class="form-control" type="search" placeholder="Cari produk..." aria-label="Cari produk">
          <button class="btn btn-outline-primary" type="submit" aria-label="Cari"><i class="bi bi-search"></i></button>
        </div>
      </form>

      <div class="d-flex align-items-center gap-2">
        <a href="#" class="btn btn-outline-secondary"><i class="bi bi-person"></i></a>
        <a href="{{ route('cart') }}" class="btn btn-primary position-relative">
          <i class="bi bi-cart3"></i>
          <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
        </a>
      </div>
    </div>
  </div>
</nav>
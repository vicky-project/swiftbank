@extends('coreui::layouts.mini-app')
@section('title', "Swift Code - $countryName")

@section('content')
<div class="container py-3">
  <div class="row justify-content-center mb-3">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('apps.swift') }}" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-2"></i>Daftar Negara
        </a>
      </div>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-12 col-lg-10">
      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">{{ $countryName }} ({{ $countryCode }})</h4>
          <small>Kode SWIFT Bank</small>
        </div>
        <div class="card-body">
          <!-- Form pencarian -->
          <form method="GET" action="{{ route('apps.swift.show', $countryCode) }}" class="mb-3" id="searchForm">
            <input type="hidden" name="initData" value="{{ request()->get('initData') }}">
            <div class="input-group">
              <input type="text" name="search" class="form-control" placeholder="Cari bank, swift code, atau kota..." value="{{ $search }}">
              <button class="btn btn-outline-primary" type="submit">
                <i class="bi bi-search"></i> Cari
              </button>
              @if($search)
              <a href="{{ route('apps.swift.show', $countryCode) }}" class="btn btn-outline-secondary" id="resetSearch">
                <i class="bi bi-x-lg"></i> Reset
              </a>
              @endif
            </div>
          </form>

          <!-- Info hasil pencarian -->
          @if($banks->total() > 0)
          <div class="text-muted small mb-2">
            Menampilkan {{ $banks->firstItem() }} - {{ $banks->lastItem() }} dari {{ $banks->total() }} bank
          </div>
          @endif

          <div class="banks-container">
            @forelse($grouped as $city => $banksInCity)
            <div class="city-group mb-5">
              <h5 class="border-start border-3 border-primary ps-3 mb-3">
                <i class="bi bi-building me-2"></i>{{ $city ?: 'Kota tidak diketahui' }}
              </h5>
              <div class="row g-3">
                @foreach($banksInCity as $bank)
                <div class="col-12 col-md-6 col-lg-4 bank-item">
                  <div class="card h-100 border-0 shadow-sm" style="background-color: var(--tg-theme-section-bg-color); transition: transform 0.2s;">
                    <div class="card-body">
                      <div class="d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <h6 class="card-title mb-0 fw-bold">{{ $bank->bank_name }}</h6>
                          <button class="btn btn-sm btn-outline-secondary copy-btn ms-2" onclick="copyToClipboard('{{ $bank->swift_code }}')">
                            <i class="bi bi-clipboard"></i>
                          </button>
                        </div>
                        <div class="text-muted small mb-2">
                          <i class="bi bi-geo-alt"></i> {{ $bank->city }}
                          @if($bank->branch)
                          <span class="ms-2"><i class="bi bi-diagram-2"></i> {{ $bank->branch }}</span>
                          @endif
                        </div>
                        <div class="mt-auto">
                          <span class="badge bg-primary swift-code">{{ $bank->swift_code }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            @empty
            <div class="text-center py-5">
              <i class="bi bi-search fs-1 text-muted"></i>
              <p class="mt-2 text-muted">
                Tidak ditemukan bank yang cocok
              </p>
            </div>
            @endforelse
          </div>

          <!-- Pagination -->
          @if($banks->hasPages())
          <div class="d-flex justify-content-center mt-4">
            {{ $banks->links() }}
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Loading Overlay Spinner -->
<div id="loadingSpinner" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
  <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>
@endsection

@push('styles')
<style>
  /* Menggunakan tema Telegram */
  body {
    background-color: var(--tg-theme-bg-color);
    color: var(--tg-theme-text-color);
  }
  .card {
    background-color: var(--tg-theme-secondary-bg-color);
    border: none;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
  .btn-primary {
    background-color: var(--tg-theme-button-color);
    border-color: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
  }
  .btn-outline-primary {
    color: var(--tg-theme-button-color);
    border-color: var(--tg-theme-button-color);
  }
  .btn-outline-primary:hover {
    background-color: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
  }
  .btn-outline-secondary {
    color: var(--tg-theme-hint-color);
    border-color: var(--tg-theme-hint-color);
  }
  .btn-outline-secondary:hover {
    background-color: var(--tg-theme-hint-color);
    color: var(--tg-theme-button-text-color);
  }
  .text-muted {
    color: var(--tg-theme-hint-color) !important;
  }
  .spinner-border {
    color: var(--tg-theme-button-color) !important;
  }
  .swift-code {
    font-family: monospace;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
  }
  .copy-btn {
    transition: all 0.2s;
    padding: 0.25rem 0.5rem;
  }
  .copy-btn:active {
    transform: scale(0.92);
  }
  /* Responsif: pada layar kecil, card mengambil lebar penuh */
  @media (max-width: 576px) {
    .bank-item {
      flex: 0 0 100%;
      max-width: 100%;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  const spinner = document.getElementById('loadingSpinner');
  const searchForm = document.getElementById('searchForm');
  const resetSearch = document.getElementById('resetSearch');

  function showSpinner() {
    spinner.style.display = 'flex';
  }

  // ================== COPY TO CLIPBOARD ==================
  function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed'; // Hindari scroll ke bawah
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
      const successful = document.execCommand('copy');
      if (successful) {
        showToast ? showToast(`Kode ${text} disalin`, 'success'): alert(`Kode ${text} disalin`);
      } else {
        throw new Error('Fallback copy gagal');
      }
    } catch (err) {
      showToast ? showToast('Gagal menyalin', 'danger'): alert('Gagal menyalin');
    }
    document.body.removeChild(textarea);
  }

  function copyToClipboard(text) {
    if (!navigator.clipboard) {
      fallbackCopy(text);
      return;
    } else {
      navigator.clipboard.writeText(text).then(() => {
      showToast(`Kode ${text} disalin`, 'success') || alert(`Kode ${text} disalin`);
      }).catch(err => {
      fallbackCopy(text);
      });
    }
  }

  searchForm.addEventListener("submit", showSpinner);
  resetSearch.addEventListener("click", showSpinner);

  document.querySelectorAll('.pagination a').forEach(link => {
  link.addEventListener('click', showSpinner);
  });
</script>
@endpush
@extends('coreui::layouts.mini-app')
@section('title', 'Swift Code Bank')

@section('content')
<div class="container py-3">
  <div class="row justify-content-center mb-3">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('telegram.home') }}" class="btn btn-outline-secondary disabled">
          <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
      </div>
    </div>
  </div>
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0"><i class="bi bi-bank2 me-2"></i>Swift Code Bank</h4>
        </div>
        <div class="card-body">
          <div class="position-relative mb-3">
            <input type="text" id="searchCountry" class="form-control" placeholder="Cari negara...">
            <button id="clearSearch" class="btn btn-link position-absolute end-0 top-0 text-muted d-none" style="padding: 0.375rem 0.75rem;">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div id="countryList">
            @foreach($countries as $country)
            <a href="{{ route('apps.swift.show', $country['code']) }}" class="text-decoration-none">
              <div class="list-group-item d-flex justify-content-between align-items-center mb-2 rounded-3 border-0 p-2" style="background-color: var(--tg-theme-section-bg-color);">
                <div>
                  <strong>{{ $country['name'] }}</strong>
                  <div class="small text-muted">
                    {{ $country['code'] }}
                  </div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
              </div>
            </a>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const searchInput = document.getElementById('searchCountry');
  const clearBtn = document.getElementById('clearSearch');
  const countryList = document.getElementById('countryList');

  function filterCountries() {
    const filter = searchInput.value.toLowerCase();
    document.querySelectorAll('#countryList > a').forEach(item => {
    const text = item.querySelector('strong').innerText.toLowerCase();
    const code = item.querySelector('.small').innerText.toLowerCase();
    item.style.display = (text.includes(filter) || code.includes(filter)) ? '' : 'none';
    });
    clearBtn.classList.toggle('d-none', searchInput.value === '');
  }

  searchInput.addEventListener('keyup', filterCountries);
  clearBtn.addEventListener('click', () => {
  searchInput.value = '';
  filterCountries();
  searchInput.focus();
  });
</script>
@endpush

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
  }
  .card-header {
    background-color: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
    border-bottom: none;
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
  .list-group-item {
    transition: background-color 0.2s;
  }
  .list-group-item:hover {
    background-color: var(--tg-theme-section-separator-color) !important;
  }
</style>
@endpush
@extends('coreui::layouts.mini-app')
@section('title', "Swift Code - {$countryName}")

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
    <div class="col-md-10 col-lg-8">
      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">{{ $countryName }} ({{ $countryCode }})</h4>
          <small>Kode SWIFT Bank</small>
        </div>
        <div class="card-body">
          <div class="position-relative mb-3">
            <input type="text" id="searchBank" class="form-control" placeholder="Cari bank, swift code, atau kota...">
            <button id="clearSearch" class="btn btn-link position-absolute end-0 top-0 text-muted d-none" style="padding: 0.375rem 0.75rem;">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div id="banksContainer">
            @foreach($grouped as $city => $banks)
            <div class="city-group mb-4" data-city="{{ $city }}">
              <h5 class="border-start border-3 border-primary ps-3 mb-3">
                <i class="bi bi-building me-2"></i>{{ $city ?: 'Kota tidak diketahui' }}
              </h5>
              <div class="row g-3">
                @foreach($banks as $bank)
                <div class="col-md-12 bank-item" data-name="{{ strtolower($bank->bank_name) }}" data-code="{{ strtolower($bank->swift_code) }}" data-city="{{ strtolower($bank->city) }}">
                  <div class="card h-100 border-0" style="background-color: var(--tg-theme-section-bg-color);">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <h6 class="card-title mb-1">{{ $bank->bank_name }}</h6>
                          <div class="text-muted small">
                            <i class="bi bi-geo-alt"></i> {{ $bank->city }}
                            @if($bank->branch)
                            <span class="ms-2"><i class="bi bi-diagram-2"></i> {{ $bank->branch }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="text-end">
                          <span class="badge bg-primary fs-6">{{ $bank->swift_code }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            @endforeach
          </div>

          <!-- Pesan jika tidak ada hasil -->
          <div id="noResult" class="text-center py-5 d-none">
            <i class="bi bi-search fs-1 text-muted"></i>
            <p class="mt-2 text-muted">
              Tidak ditemukan bank yang cocok
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const searchInput = document.getElementById('searchBank');
  const clearBtn = document.getElementById('clearSearch');
  const bankItems = document.querySelectorAll('.bank-item');
  const cityGroups = document.querySelectorAll('.city-group');
  const noResultDiv = document.getElementById('noResult');

  function filterBanks() {
    const keyword = searchInput.value.toLowerCase().trim();
    let visibleCount = 0;

    cityGroups.forEach(group => {
    let groupVisible = false;
    const banksInGroup = group.querySelectorAll('.bank-item');
    banksInGroup.forEach(bank => {
    const name = bank.getAttribute('data-name') || '';
    const code = bank.getAttribute('data-code') || '';
    const city = bank.getAttribute('data-city') || '';
    const matches = keyword === '' || name.includes(keyword) || code.includes(keyword) || city.includes(keyword);
    if (matches) {
    bank.style.display = '';
    groupVisible = true;
    visibleCount++;
    } else {
    bank.style.display = 'none';
    }
    });
    // Tampilkan/sembunyikan seluruh group berdasarkan apakah ada bank yang terlihat
    group.style.display = groupVisible ? '' : 'none';
    });

    // Tampilkan pesan "tidak ditemukan" jika tidak ada hasil
    if (visibleCount === 0 && keyword !== '') {
      noResultDiv.classList.remove('d-none');
    } else {
      noResultDiv.classList.add('d-none');
    }

    clearBtn.classList.toggle('d-none', keyword === '');
  }

  searchInput.addEventListener('keyup', filterBanks);
  clearBtn.addEventListener('click', () => {
  searchInput.value = '';
  filterBanks();
  searchInput.focus();
  });
</script>
@endpush

@push('styles')
<style>
  .city-group {
    transition: all 0.2s;
  }
  .bank-item {
    transition: all 0.2s;
  }
  .badge {
    font-family: monospace;
    letter-spacing: 0.5px;
  }
</style>
@endpush
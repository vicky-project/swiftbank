@extends('telegram::layouts.mini-app')

@section('title', 'Swift Code Bank')

@section('content')
<div class="container py-0" style="max-width:600px; margin:0 auto;">
  <div id="swift-app">
    <div id="countries-view" style="display:none;"></div>
    <div id="banks-view" style="display:none;"></div>
  </div>
</div>
@endsection

@push('styles')
<style>
  body {
    background-color: var(--tg-theme-bg-color) !important;
    color: var(--tg-theme-text-color) !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  .container {
    padding-left: 0 !important;
    padding-right: 0 !important;
  }
  .card {
    background-color: var(--tg-theme-secondary-bg-color) !important;
    border-color: var(--tg-theme-section-separator-color) !important;
    border-radius: 0;
  }
  .card-header {
    background-color: var(--tg-theme-button-color) !important;
    color: var(--tg-theme-button-text-color) !important;
    border-radius: 0;
  }
  .form-control, .input-group-text {
    background-color: var(--tg-theme-bg-color) !important;
    color: var(--tg-theme-text-color) !important;
    border-color: var(--tg-theme-section-separator-color) !important;
  }
  .btn-primary {
    background-color: var(--tg-theme-button-color) !important;
    border-color: var(--tg-theme-button-color) !important;
    color: var(--tg-theme-button-text-color) !important;
  }
  .btn-outline-secondary {
    border-color: var(--tg-theme-section-separator-color) !important;
    color: var(--tg-theme-hint-color) !important;
  }
  .text-muted {
    color: var(--tg-theme-hint-color) !important;
  }
  .list-group-item {
    background-color: var(--tg-theme-secondary-bg-color) !important;
    border-color: var(--tg-theme-section-separator-color) !important;
    color: var(--tg-theme-text-color) !important;
  }
  .bank-item .card {
    transition: transform 0.2s;
  }
  .bank-item .card:active {
    transform: scale(0.98);
  }
  .swift-code {
    font-family: monospace;
    letter-spacing: 0.5px;
  }
  .copy-btn {
    padding: 0.25rem 0.5rem;
  }
  .copy-btn:active {
    transform: scale(0.92);
  }
  .pagination-wrapper {
    overflow-x: auto;
    text-align: center;
    margin: 1rem 0;
  }
  .pagination {
    display: inline-flex;
    flex-wrap: nowrap;
    gap: 0.25rem;
  }
  .page-item {
    flex-shrink: 0;
  }
  mark {
    background-color: #ffeb3b;
    color: #000;
    border-radius: 3px;
    padding: 0 2px;
  }
  @media (prefers-color-scheme: dark) {
    mark {
      background-color: #f9a825;
      color: #1a1a1a;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  (function() {
  const { fetchWithAuth, showToast, showLoading, hideLoading, escapeHtml, renderPagination, copyToClipboard } = window.TelegramApp;

  let allCountries = [];
  let currentCountryCode = null;
  let currentPage = 1;
  let currentSearch = '';

  // Render daftar negara
  async function renderCountries() {
  showLoading('Memuat daftar negara...');
  try {
  allCountries = await fetchWithAuth('{{ config("app.url") }}/api/swift/countries');
  const container = document.getElementById('countries-view');
  let html = `
  <div class="card shadow">
  <div class="card-header">
  <h4 class="mb-0"><i class="bi bi-bank2 me-2"></i>Swift Code Bank</h4>
  </div>
  <div class="card-body">
  <div class="mb-3">
  <input type="text" id="searchCountry" class="form-control" placeholder="Cari negara...">
  </div>
  <div id="countriesList"></div>
  </div>
  </div>
  `;
  container.innerHTML = html;
  renderCountriesList(allCountries, '');
  document.getElementById('searchCountry').addEventListener('input', (e) => {
  renderCountriesList(allCountries, e.target.value);
  });
  document.getElementById('countries-view').style.display = 'block';
  document.getElementById('banks-view').style.display = 'none';
  } catch (err) {
  showToast('Gagal memuat negara: ' + err.message);
  document.getElementById('countries-view').innerHTML = `<div class="alert alert-danger">Gagal memuat negara: ${err.message}</div>`;
  } finally {
  hideLoading();
  }
  }

  function renderCountriesList(countries, filter) {
  const listContainer = document.getElementById('countriesList');
  const term = filter.toLowerCase();
  const filtered = countries.filter(c =>
  c.name.toLowerCase().includes(term) ||
  c.code.toLowerCase().includes(term)
  );
  if (filtered.length === 0) {
  listContainer.innerHTML = '<div class="text-center text-muted py-4">Tidak ada negara</div>';
  return;
  }
  let html = '';
  filtered.forEach(country => {
  html += `
  <div class="list-group-item d-flex justify-content-between align-items-center mb-2 rounded-3 border-0 p-2" style="cursor:pointer;" data-code="${escapeHtml(country.code)}">
  <div>
  <strong>${escapeHtml(country.name)}</strong>
  <div class="small text-muted">${escapeHtml(country.code)}</div>
  </div>
  <i class="bi bi-chevron-right"></i>
  </div>
  `;
  });
  listContainer.innerHTML = html;
  document.querySelectorAll('.list-group-item[data-code]').forEach(el => {
  el.addEventListener('click', () => {
  const code = el.dataset.code;
  loadBanks(code);
  });
  });
  }

  // Load banks for a country
  async function loadBanks(countryCode, page = 1, search = '') {
  showLoading('Memuat data bank...');
  currentCountryCode = countryCode;
  currentPage = page;
  currentSearch = search;
  try {
  let url = `{{ config("app.url") }}/api/swift/banks/${countryCode}?page=${page}`;
  if (search) url += `&search=${encodeURIComponent(search)}`;
  const data = await fetchWithAuth(url);
  renderBanksView(data, countryCode, page, search);
  } catch (err) {
  showToast('Gagal memuat bank: ' + err.message);
  renderCountries();
  } finally {
  hideLoading();
  }
  }

  function renderBanksView(data, countryCode, page, search) {
  const countryName = data.country_name;
  const banks = data.banks; // pagination object
  const grouped = banks.data.reduce((acc, bank) => {
  const city = bank.city || 'Kota tidak diketahui';
  if (!acc[city]) acc[city] = [];
  acc[city].push(bank);
  return acc;
  }, {});

  let html = `
  <div class="mb-3">
  <button id="backToCountriesBtn" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Daftar Negara</button>
  </div>
  <div class="card shadow">
  <div class="card-header">
  <h4 class="mb-0">${escapeHtml(countryName)} (${escapeHtml(countryCode)})</h4>
  <small>Kode SWIFT Bank</small>
  </div>
  <div class="card-body">
  <form id="searchForm" class="mb-3">
  <div class="input-group">
  <input type="text" id="searchBank" class="form-control" placeholder="Cari bank, swift code, atau kota..." value="${escapeHtml(search)}">
  <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
  </div>
  </form>
  ${banks.total > 0 ? `<div class="text-muted small mb-2">Menampilkan ${banks.from} - ${banks.to} dari ${banks.total} bank</div>` : ''}
  <div id="banksList"></div>
  <div id="paginationContainer"></div>
  </div>
  </div>
  `;
  document.getElementById('banks-view').innerHTML = html;
  document.getElementById('countries-view').style.display = 'none';
  document.getElementById('banks-view').style.display = 'block';

  renderBanksList(grouped, search);
  if (banks.last_page > 1) {
  renderPagination('paginationContainer', banks.current_page, banks.last_page, (newPage) => {
  loadBanks(countryCode, newPage, search);
  });
  } else {
  document.getElementById('paginationContainer').innerHTML = '';
  }

  document.getElementById('backToCountriesBtn').addEventListener('click', () => {
  renderCountries();
  });
  document.getElementById('searchForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const newSearch = document.getElementById('searchBank').value;
  loadBanks(countryCode, 1, newSearch);
  });
  }

  function renderBanksList(grouped, searchTerm) {
  const container = document.getElementById('banksList');
  if (Object.keys(grouped).length === 0) {
  container.innerHTML = '<div class="text-center py-4 text-muted">Tidak ada bank ditemukan</div>';
  return;
  }
  let html = '';
  for (const [city, banks] of Object.entries(grouped)) {
  html += `
  <div class="city-group mb-4">
  <h5 class="border-start border-3 border-primary ps-3 mb-3"><i class="bi bi-building me-2"></i>${escapeHtml(city)}</h5>
  <div class="row g-3">
  `;
  banks.forEach(bank => {
  html += `
  <div class="col-12 col-md-6 bank-item">
  <div class="card h-100 border-0 shadow-sm">
  <div class="card-body">
  <div class="d-flex justify-content-between align-items-start mb-2">
  <h6 class="card-title mb-0 fw-bold">${escapeHtml(bank.bank_name)}</h6>
  <button class="btn btn-sm btn-outline-secondary copy-btn" data-code="${escapeHtml(bank.swift_code)}">
  <i class="bi bi-clipboard"></i>
  </button>
  </div>
  <div class="text-muted small mb-2">
  <i class="bi bi-geo-alt"></i> ${escapeHtml(bank.city)}
  ${bank.branch ? `<span class="ms-2"><i class="bi bi-diagram-2"></i> ${escapeHtml(bank.branch)}</span>` : ''}
  </div>
  <div class="mt-auto">
  <span class="badge bg-primary swift-code">${escapeHtml(bank.swift_code)}</span>
  </div>
  </div>
  </div>
  </div>
  `;
  });
  html += `</div></div>`;
  }
  container.innerHTML = html;
  // Attach copy events
  document.querySelectorAll('.copy-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
  e.stopPropagation();
  const code = btn.dataset.code;
  copyToClipboard(code);
  });
  });
  if (searchTerm) highlightSearch(container, searchTerm);
  }

  function highlightSearch(container, term) {
  const regex = new RegExp(`(${term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
  container.querySelectorAll('.card-title, .text-muted, .swift-code').forEach(el => {
  const original = el.innerText;
  const highlighted = original.replace(regex, '<mark>$1</mark>');
  el.innerHTML = highlighted;
  });
  }

  // Start
  renderCountries();
  })();
</script>
@endpush
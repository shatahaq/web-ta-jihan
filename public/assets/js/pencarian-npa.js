(() => {
  const form = document.getElementById('npa-search-form');
  const input = document.getElementById('npa-search');
  const result = document.getElementById('npa-search-result');
  const loading = document.getElementById('npa-search-loading');
  if (!form || !input || !result || !loading) return;

  const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
  }[character]));

  const formatCurrency = (value) => `Rp ${new Intl.NumberFormat('id-ID', {
    maximumFractionDigits: 0
  }).format(Number(value || 0))}`;

  const renderEmpty = (title, description) => {
    result.innerHTML = `<section class="ui-card px-6 py-12 text-center" role="status">
      <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-600" aria-hidden="true">⌕</div>
      <h2 class="font-semibold text-slate-800">${escapeHtml(title)}</h2>
      <p class="mt-1 text-sm text-slate-500">${escapeHtml(description)}</p>
    </section>`;
  };

  const field = (label, value, extraClass = '') => `<div><p class="result-label">${escapeHtml(label)}</p><p class="result-value ${extraClass}">${escapeHtml(value || '—')}</p></div>`;
  const actionLink = (href, label) => `<div class="mt-6 flex justify-end"><a href="${href}" class="result-button">${escapeHtml(label)} <span aria-hidden="true">→</span></a></div>`;

  function renderActive(data) {
    const bill = data.tagihan || {};
    return `<div class="grid gap-4 sm:grid-cols-3">
      ${field('Jumlah Tagihan', bill.jumlah_tagihan || 0)}
      ${field('Belum Lunas', `${bill.belum_lunas || 0} tagihan`)}
      ${field('Total Tunggakan', formatCurrency(bill.total_tunggakan))}
    </div>${actionLink(`${window.APP_URL}tagihan?q=${encodeURIComponent(data.pelanggan.npa)}`, 'Lihat detail tagihan')}`;
  }

  function renderDisconnection(data) {
    const disconnection = data.pemutusan;
    if (!disconnection) return '<p class="mt-5 rounded-lg bg-white/70 p-4 text-sm text-slate-600">Belum ada data tindakan pemutusan untuk pelanggan ini.</p>';
    return `<div class="grid gap-4 sm:grid-cols-2">
      ${field('Tanggal Pemutusan', disconnection.tgl_pemutusan)}
      ${field('Status Pemutusan', disconnection.status_pemutusan)}
      ${field('Jenis Tindakan', disconnection.jenis_tindakan)}
      ${field('Biaya Tindakan', formatCurrency(disconnection.biaya_tindakan))}
    </div>${actionLink(`${window.APP_URL}pemutusan?q=${encodeURIComponent(data.pelanggan.npa)}`, 'Lihat detail pemutusan')}`;
  }

  function renderReconnection(data) {
    const registration = data.daftar_ulang;
    const status = registration ? registration.status_verifikasi : 'Belum daftar ulang';
    let action = '';
    if (registration) action = actionLink(`${window.APP_URL}daftar-ulang/${encodeURIComponent(registration.no_registrasi)}`, 'Lihat pengajuan');
    if (!registration && window.APP_ROLE === 'Admin') action = actionLink(`${window.APP_URL}daftar-ulang/create?npa=${encodeURIComponent(data.pelanggan.npa)}`, 'Ajukan daftar ulang');
    return `<div class="grid gap-4 sm:grid-cols-2">
      ${field('Status Daftar Ulang', status)}
      ${field('Biaya Daftar Ulang', registration ? formatCurrency(registration.biaya_daftar_ulang) : '—')}
    </div>${action}`;
  }

  function renderCustomer(data) {
    const customer = data.pelanggan || {};
    const category = data.kategori || {};
    const presentation = {
      aktif: { background: 'bg-emerald-50', border: 'border-emerald-200', text: 'text-emerald-800', icon: '✓', body: renderActive },
      nonaktif_baru: { background: 'bg-amber-50', border: 'border-amber-200', text: 'text-amber-800', icon: '!', body: renderDisconnection },
      nonaktif_lama: { background: 'bg-red-50', border: 'border-red-200', text: 'text-red-800', icon: '×', body: renderReconnection }
    }[category.key] || { background: 'bg-slate-50', border: 'border-slate-200', text: 'text-slate-800', icon: 'i', body: () => '' };

    result.innerHTML = `<article class="overflow-hidden rounded-xl border ${presentation.border} bg-white shadow-card" aria-live="polite">
      <header class="${presentation.background} px-6 py-5 sm:px-7"><div class="flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white font-bold ${presentation.text}" aria-hidden="true">${presentation.icon}</span>
        <div><p class="text-xs font-bold uppercase tracking-wider ${presentation.text}">Status pelanggan</p><h2 class="mt-1 text-xl font-bold ${presentation.text}">${escapeHtml(category.label)}</h2></div>
      </div></header>
      <div class="p-6 sm:p-7"><div class="mb-6 grid gap-4 border-b border-slate-100 pb-6 sm:grid-cols-3">
        ${field('Nama Pelanggan', customer.nama_pelanggan)}
        ${field('NPA', customer.npa, 'font-mono')}
        ${field('Alamat', customer.alamat)}
      </div>${presentation.body(data)}</div>
    </article>`;
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const npa = input.value.trim();
    if (!npa) {
      window.showToast?.('warning', 'Masukkan NPA pelanggan terlebih dahulu.');
      input.focus();
      return;
    }
    loading.classList.remove('hidden');
    result.setAttribute('aria-busy', 'true');
    result.innerHTML = '';
    try {
      const response = await fetch(`${window.APP_URL}api/pelanggan/${encodeURIComponent(npa)}/status`, { headers: { Accept: 'application/json' } });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload.message || 'Data pelanggan tidak ditemukan.');
      renderCustomer(payload.data);
    } catch (error) {
      renderEmpty('Data pelanggan tidak ditemukan', 'Periksa kembali NPA yang dimasukkan, lalu coba lagi.');
    } finally {
      loading.classList.add('hidden');
      result.removeAttribute('aria-busy');
    }
  });
})();

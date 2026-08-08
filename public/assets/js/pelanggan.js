(() => {
  const form = document.querySelector('[data-pelanggan-form]'); if (!form) return;
  const status = form.querySelector('#status'), wrapper = form.querySelector('#tanggal-nonaktif-wrap'), date = form.querySelector('#tgl_nonaktif');
  const sync = () => { const show = status.value !== 'Aktif'; wrapper.classList.toggle('hidden', !show); date.required = show; if (!show) date.value = ''; };
  status.addEventListener('change', sync); sync();
})();

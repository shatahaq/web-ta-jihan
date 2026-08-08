(() => {
  const icons = {
    grid:'<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>',
    users:'<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    search:'<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>',
    receipt:'<svg viewBox="0 0 24 24"><path d="M4 2v20l3-2 3 2 3-2 3 2 3-2V2l-3 2-3-2-3 2-3-2-3 2Z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>',
    cut:'<svg viewBox="0 0 24 24"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="m8.6 8.6 12 12M8.6 15.4 20.6 3.4"/></svg>',
    repeat:'<svg viewBox="0 0 24 24"><path d="m17 1 4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>',
    chart:'<svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m7 16 4-5 3 3 5-7"/></svg>',
    settings:'<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.04 1.56V20.3h-3v-.08A1.7 1.7 0 0 0 10.66 18.7a1.7 1.7 0 0 0-1.88.34l-.06.06L6.6 16.98l.06-.06A1.7 1.7 0 0 0 7 15.04a1.7 1.7 0 0 0-1.56-1.04h-.08v-3h.08A1.7 1.7 0 0 0 7 9.96a1.7 1.7 0 0 0-.34-1.88L6.6 8.02 8.72 5.9l.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 11.7 4.74v-.08h3v.08a1.7 1.7 0 0 0 1.04 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.12 2.12-.06.06A1.7 1.7 0 0 0 19.4 10c.2.64.8 1.04 1.48 1.04h.08v3h-.08A1.7 1.7 0 0 0 19.4 15Z"/></svg>',
    logout:'<svg viewBox="0 0 24 24"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M21 3v18H9"/></svg>',
    menu:'<svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>',
    bell:'<svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>',
    check:'<svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>',
    clock:'<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    alert:'<svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/></svg>'
  };
  document.querySelectorAll('.menu-icon[data-icon]').forEach((el) => el.innerHTML = icons[el.dataset.icon] || '');
  const showToast = (type, message) => {
    const current = document.getElementById('toast'); if (current) current.remove();
    const toast = document.createElement('div'); toast.id = 'toast'; toast.className = `toast-${type || 'info'}`;
    const text = document.createElement('p'); text.className = 'text-sm font-semibold leading-5'; text.textContent = message; toast.append(text); document.body.append(toast);
    requestAnimationFrame(() => toast.classList.add('show')); setTimeout(() => { toast.classList.remove('show'); setTimeout(()=>toast.remove(),300); }, 4500);
  };
  window.showToast = showToast;
  const initialToast = document.getElementById('toast-data'); if (initialToast) showToast(initialToast.dataset.type, initialToast.dataset.message);
  document.querySelectorAll('.password-toggle').forEach((button) => button.addEventListener('click', () => { const input = button.parentElement.querySelector('input'); input.type = input.type === 'password' ? 'text' : 'password'; button.textContent = input.type === 'password' ? 'Lihat' : 'Sembunyikan'; }));
  const sidebar = document.getElementById('sidebar'), overlay = document.getElementById('sidebar-overlay'); const open = () => { sidebar?.classList.remove('-translate-x-full'); overlay?.classList.remove('hidden'); }; const close = () => { sidebar?.classList.add('-translate-x-full'); overlay?.classList.add('hidden'); };
  document.getElementById('sidebar-open')?.addEventListener('click', open); document.getElementById('sidebar-close')?.addEventListener('click', close); overlay?.addEventListener('click', close);
  document.querySelectorAll('form[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => { if (form.dataset.confirmed) return; event.preventDefault(); const modal = document.createElement('div'); modal.id='confirm-dialog'; modal.innerHTML='<div class="confirm-card"><h2 class="text-lg font-bold text-slate-900">Konfirmasi tindakan</h2><p class="mt-2 text-sm leading-6 text-slate-600"></p><div class="mt-6 flex justify-end gap-3"><button type="button" class="cancel rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Batal</button><button type="button" class="confirm rounded-xl bg-danger px-4 py-2.5 text-sm font-semibold text-white">Lanjutkan</button></div></div>'; modal.querySelector('p').textContent=form.dataset.confirm; modal.querySelector('.cancel').onclick=()=>modal.remove(); modal.querySelector('.confirm').onclick=()=>{ form.dataset.confirmed='1'; modal.remove(); form.requestSubmit(); }; document.body.append(modal); }));
  window.csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
})();

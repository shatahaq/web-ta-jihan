<?php $nav = [
    ['/dashboard', 'Dashboard', 'grid'], ['/pelanggan', 'Data Pelanggan', 'users'], ['/pencarian-npa', 'Pencarian NPA', 'search'], ['/tagihan', 'Tagihan', 'receipt'], ['/pemutusan', 'Pemutusan', 'cut'], ['/daftar-ulang', 'Daftar Ulang', 'repeat'], ['/laporan', 'Laporan', 'chart'],
]; ?>
<div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-slate-950/40 lg:hidden"></div>
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-primary text-white shadow-2xl transition-transform lg:translate-x-0" aria-label="Navigasi utama">
    <div class="flex h-24 items-center gap-3 border-b border-white/10 px-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white p-1.5"><img src="<?= e(asset('images/logo.webp')) ?>" alt="Logo Tirtanadi" class="h-full w-full object-contain"></div>
        <div><p class="text-sm font-semibold tracking-wide">PERUMDA TIRTANADI</p><p class="text-xs text-blue-200">Cabang Medan Denai</p></div>
        <button id="sidebar-close" class="ml-auto rounded-lg p-2 text-blue-100 hover:bg-white/10 lg:hidden" aria-label="Tutup menu">×</button>
    </div>
    <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
        <p class="mb-3 px-3 text-[11px] font-bold tracking-[.15em] text-blue-300">MENU UTAMA</p>
        <?php foreach ($nav as [$path, $label, $icon]): $active = is_active($path); ?>
            <a href="<?= e(url($path)) ?>" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition <?= $active ? 'bg-white text-primary shadow-lg' : 'text-blue-100 hover:bg-white/10 hover:text-white' ?>">
                <span class="menu-icon" data-icon="<?= e($icon) ?>"></span><?= e($label) ?>
            </a>
        <?php endforeach; ?>
        <div class="my-5 border-t border-white/10"></div>
        <p class="mb-3 px-3 text-[11px] font-bold tracking-[.15em] text-blue-300">SISTEM</p>
        <a href="<?= e(url('/dashboard')) ?>" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-blue-100 hover:bg-white/10"><span class="menu-icon" data-icon="settings"></span>Pengaturan Akun</a>
    </nav>
    <div class="border-t border-white/10 p-4"><form method="post" action="<?= e(url('/logout')) ?>"><?= csrf_field() ?><button class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-blue-100 hover:bg-white/10 hover:text-white"><span class="menu-icon" data-icon="logout"></span>Keluar dari Sistem</button></form></div>
</aside>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Informasi Status Pemutusan dan Daftar Ulang Pelanggan Nonaktif Tirtanadi Cabang Medan Denai">
    <meta name="csrf-token" content="<?= e(Auth::token()) ?>">
    <title><?= e($title ?? config('name')) ?> · Tirtanadi</title>
    <script>tailwind = { config: { theme: { extend: { colors: { primary: '#0b3a69', 'primary-dark': '#072c52', success: '#168047', warning: '#b56a00', danger: '#b42318', background: '#f3f8fc' }, boxShadow: { card: '0 8px 30px rgba(15, 53, 87, .08)' } } } } };</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="min-h-screen bg-background text-slate-800">
    <?php require root_path('app/Views/components/sidebar.php'); ?>
    <div class="min-h-screen lg:pl-72">
        <?php require root_path('app/Views/components/topbar.php'); ?>
        <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto max-w-7xl">
                <?php require root_path('app/Views/components/breadcrumb.php'); ?>
                <?php require $contentView; ?>
            </div>
        </main>
        <footer class="px-4 pb-6 text-center text-xs text-slate-500 lg:px-8">© <?= date('Y') ?> Perumda Tirtanadi · Cabang Medan Denai</footer>
    </div>
    <?php $toast = Session::consumeFlash('toast'); if ($toast): ?><div id="toast-data" data-type="<?= e($toast['type']) ?>" data-message="<?= e($toast['message']) ?>"></div><?php endif; ?>
    <script>window.APP_URL = <?= json_encode(url()) ?>; window.APP_ROLE = <?= json_encode(Auth::user()['role'] ?? '') ?>;</script>
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
    <?php if (isset($pageScript)): ?><script src="<?= e(asset('js/' . $pageScript)) ?>" defer></script><?php endif; ?>
</body>
</html>

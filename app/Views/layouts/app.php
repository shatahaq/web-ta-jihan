<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Informasi Status Pemutusan dan Daftar Ulang Pelanggan Nonaktif Tirtanadi Cabang Medan Denai">
    <meta name="csrf-token" content="<?= e(Auth::token()) ?>">
    <title><?= e($title ?? config('name')) ?> · Tirtanadi</title>
    <script>tailwind = { config: { theme: { extend: { fontFamily: { sans: ['Inter','system-ui','sans-serif'] }, colors: { primary: '#0b3a69', 'primary-dark': '#082b4c', success: '#137a4a', warning: '#a85f00', danger: '#b42318', background: '#f3f7fb' } } } } };</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="min-h-screen bg-background text-slate-800">
    <?php require root_path('app/Views/components/sidebar.php'); ?>
    <div class="app-main">
        <?php require root_path('app/Views/components/topbar.php'); ?>
        <main>
            <div class="app-content">
                <?php require root_path('app/Views/components/breadcrumb.php'); ?>
                <?php require $contentView; ?>
            </div>
        </main>
        <footer class="app-footer">© <?= date('Y') ?> Perumda Tirtanadi · Cabang Medan Denai</footer>
    </div>
    <?php $toast = Session::consumeFlash('toast'); if ($toast): ?><div id="toast-data" data-type="<?= e($toast['type']) ?>" data-message="<?= e($toast['message']) ?>"></div><?php endif; ?>
    <script>window.APP_URL = <?= json_encode(url()) ?>; window.APP_ROLE = <?= json_encode(Auth::user()['role'] ?? '') ?>;</script>
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
    <?php if (isset($pageScript)): ?><script src="<?= e(asset('js/' . $pageScript)) ?>" defer></script><?php endif; ?>
</body>
</html>

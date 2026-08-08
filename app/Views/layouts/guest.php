<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Informasi Tirtanadi Cabang Medan Denai">
    <title><?= e($title ?? config('name')) ?> · Tirtanadi</title>
    <script>tailwind = { config: { theme: { extend: { colors: { primary: '#0b3a69', 'primary-dark': '#072c52', success: '#168047', warning: '#b56a00', danger: '#b42318', background: '#f3f8fc' }, boxShadow: { card: '0 8px 30px rgba(15, 53, 87, .08)' } } } } };</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="min-h-screen bg-background text-slate-800">
    <?php require $contentView; ?>
    <?php $toast = Session::consumeFlash('toast'); if ($toast): ?><div id="toast-data" data-type="<?= e($toast['type']) ?>" data-message="<?= e($toast['message']) ?>"></div><?php endif; ?>
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Informasi Tirtanadi Cabang Medan Denai">
    <title><?= e($title ?? config('name')) ?> · Tirtanadi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }, colors: { primary: '#123b62', 'primary-dark': '#0d2d4b', success: '#167c49', warning: '#a66308', danger: '#b42318', background: '#f4f7fa' } } } };</script>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="min-h-screen bg-background font-sans text-slate-800">
    <?php require $contentView; ?>
    <?php $toast = Session::consumeFlash('toast'); if ($toast): ?><div id="toast-data" data-type="<?= e($toast['type']) ?>" data-message="<?= e($toast['message']) ?>"></div><?php endif; ?>
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>

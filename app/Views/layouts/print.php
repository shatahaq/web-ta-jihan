<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Laporan') ?> · Tirtanadi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="bg-white text-slate-900 print:bg-white">
    <main class="mx-auto max-w-7xl p-6 print:max-w-none print:p-0"><?php require $contentView; ?></main>
    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>

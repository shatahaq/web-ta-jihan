<?php

$router->get('/', [AuthController::class, 'login']);
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/pelanggan', [PelangganController::class, 'index']);
$router->get('/pelanggan/create', [PelangganController::class, 'create']);
$router->post('/pelanggan', [PelangganController::class, 'store']);
$router->get('/pelanggan/{npa}/edit', [PelangganController::class, 'edit']);
$router->put('/pelanggan/{npa}', [PelangganController::class, 'update']);
$router->delete('/pelanggan/{npa}', [PelangganController::class, 'destroy']);
$router->get('/pelanggan/{npa}', [PelangganController::class, 'show']);

$router->get('/tagihan', [TagihanController::class, 'index']);
$router->get('/tagihan/create', [TagihanController::class, 'create']);
$router->post('/tagihan', [TagihanController::class, 'store']);
$router->get('/tagihan/{id}/edit', [TagihanController::class, 'edit']);
$router->put('/tagihan/{id}', [TagihanController::class, 'update']);

$router->get('/pemutusan', [PemutusanController::class, 'index']);
$router->get('/pemutusan/create', [PemutusanController::class, 'create']);
$router->post('/pemutusan', [PemutusanController::class, 'store']);
$router->get('/pemutusan/{id}/edit', [PemutusanController::class, 'edit']);
$router->put('/pemutusan/{id}', [PemutusanController::class, 'update']);

$router->get('/pencarian-npa', [PencarianNpaController::class, 'index']);
$router->get('/daftar-ulang', [DaftarUlangController::class, 'index']);
$router->get('/daftar-ulang/create', [DaftarUlangController::class, 'create']);
$router->post('/daftar-ulang', [DaftarUlangController::class, 'store']);
$router->get('/daftar-ulang/{registration}/bukti', [DaftarUlangController::class, 'bukti']);
$router->post('/daftar-ulang/{registration}/approve', [DaftarUlangController::class, 'approve']);
$router->post('/daftar-ulang/{registration}/reject', [DaftarUlangController::class, 'reject']);
$router->get('/daftar-ulang/{registration}', [DaftarUlangController::class, 'show']);

$router->get('/laporan', [LaporanController::class, 'index']);
$router->get('/laporan/print', [LaporanController::class, 'print']);

$router->get('/staff', [StaffController::class, 'index']);
$router->get('/staff/create', [StaffController::class, 'create']);
$router->post('/staff', [StaffController::class, 'store']);
$router->delete('/staff/{id}', [StaffController::class, 'destroy']);
$router->get('/riwayat-aktivitas', [ActivityLogController::class, 'index']);

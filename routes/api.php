<?php

$router->get('/api/pelanggan/search', [ApiController::class, 'customerSearch']);
$router->get('/api/pelanggan/{npa}/tagihan', [ApiController::class, 'tagihan']);
$router->get('/api/pelanggan/{npa}/status', [ApiController::class, 'status']);
$router->get('/api/pelanggan/{npa}/pemutusan', [ApiController::class, 'pemutusan']);
$router->get('/api/pelanggan/{npa}/daftar-ulang', [ApiController::class, 'daftarUlang']);
$router->get('/api/pelanggan/{npa}', [ApiController::class, 'customer']);
$router->post('/api/pelanggan', [ApiController::class, 'createPelanggan']);
$router->put('/api/pelanggan/{npa}', [ApiController::class, 'updatePelanggan']);
$router->delete('/api/pelanggan/{npa}', [ApiController::class, 'deletePelanggan']);
$router->post('/api/pemutusan', [ApiController::class, 'createPemutusan']);
$router->put('/api/pemutusan/{id}', [ApiController::class, 'updatePemutusan']);
$router->post('/api/daftar-ulang', [ApiController::class, 'createDaftarUlang']);
$router->post('/api/daftar-ulang/{registration}/approve', [ApiController::class, 'approveDaftarUlang']);
$router->post('/api/daftar-ulang/{registration}/reject', [ApiController::class, 'rejectDaftarUlang']);

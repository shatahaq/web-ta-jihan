<?php

declare(strict_types=1);

final class PencarianNpaController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->view('pencarian/index', ['title' => 'Pencarian NPA']);
    }
}

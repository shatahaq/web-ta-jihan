<?php

declare(strict_types=1);

function hariNonaktif(?string $tanggal): int
{
    if (!$tanggal) return 0;
    return max(0, (int) (new DateTime($tanggal))->diff(new DateTime('today'))->format('%r%a'));
}

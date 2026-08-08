<?php

declare(strict_types=1);

function canManage(): bool
{
    return Auth::isAdmin();
}

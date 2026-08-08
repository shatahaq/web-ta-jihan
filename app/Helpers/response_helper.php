<?php

declare(strict_types=1);

function apiResponse(array $data, int $status = 200): never
{
    Controller::json($data, $status);
}

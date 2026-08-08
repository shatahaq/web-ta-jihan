<?php

declare(strict_types=1);

final class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $message): self
    {
        if (trim((string) ($this->data[$field] ?? '')) === '') {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function max(string $field, int $length, string $message): self
    {
        $len = function_exists('mb_strlen') ? mb_strlen((string) ($this->data[$field] ?? '')) : strlen((string) ($this->data[$field] ?? ''));
        if ($len > $length) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $message): self
    {
        if (($this->data[$field] ?? null) !== null && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function date(string $field, string $message, bool $nullable = true): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));
        if ($value === '' && $nullable) return $this;
        $date = DateTime::createFromFormat('Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) $this->errors[$field] = $message;
        return $this;
    }

    public function numeric(string $field, string $message, float $min = 0): self
    {
        $value = $this->data[$field] ?? null;
        if (!is_numeric($value) || (float) $value < $min) $this->errors[$field] = $message;
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}

<?php

namespace MageOS\AsyncEvents\Api\Data;

interface ResultInterface
{
    public function getIsSuccessful(): bool;

    public function setIsSuccessful(bool $isSuccessful): void;

    public function getIsRetryable(): bool;

    public function setIsRetryable(bool $isRetryable): void;

    public function getRetryAfter(): int;

    public function setRetryAfter(int $retryAfter): void;

    public function isSuccessful(): bool;

    public function isRetryable(): bool;
}

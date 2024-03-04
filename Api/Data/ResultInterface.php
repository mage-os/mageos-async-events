<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Api\Data;

interface ResultInterface
{
    /**
     * Getter for is_successful
     *
     * @return bool
     */
    public function getIsSuccessful(): bool;

    /**
     * Setter for is_successful
     *
     * @param bool $isSuccessful
     * @return void
     */
    public function setIsSuccessful(bool $isSuccessful): void;

    /**
     * Getter for is_retryable
     *
     * @return bool
     */
    public function getIsRetryable(): bool;

    /**
     * Setter for is_retryable
     *
     * @param bool $isRetryable
     * @return void
     */
    public function setIsRetryable(bool $isRetryable): void;

    /**
     *  Getter for retry_after
     *
     * @return int|null
     */
    public function getRetryAfter(): ?int;

    /**
     * Setter for retry_after
     *
     * @param int $retryAfter
     * @return void
     */
    public function setRetryAfter(int $retryAfter): void;
}

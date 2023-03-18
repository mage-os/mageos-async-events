<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use Magento\Framework\Exception\LocalizedException;

class NotifierFactory implements NotifierFactoryInterface
{
    /**
     * @param array $notifierClasses
     */
    public function __construct(private readonly array $notifierClasses = [])
    {
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function create(string $type): NotifierInterface
    {
        $notifier = $this->notifierClasses[$type] ?? null;

        if (!$notifier instanceof NotifierInterface) {
            throw new LocalizedException(__("Cannot instantiate a notifier for $notifier"));
        }

        return $notifier;
    }
}

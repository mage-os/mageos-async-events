<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use InvalidArgumentException;

class NotifierFactory implements NotifierFactoryInterface
{
    /**
     * @var array
     */
    private $notifierClasses;

    /**
     * @param array $notifierClasses
     */
    public function __construct(array $notifierClasses = [])
    {
        $this->notifierClasses = $notifierClasses;
    }

    /**
     * @inheritDoc
     */
    public function create(string $type): NotifierInterface
    {
        $notifier = $this->notifierClasses[$type] ?? null;

        if (!$notifier instanceof NotifierInterface) {
            throw new InvalidArgumentException(__("Cannot instantiate a notifier for $notifier"));
        }

        return $notifier;
    }
}

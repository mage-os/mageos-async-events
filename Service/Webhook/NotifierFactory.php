<?php

namespace Aligent\Webhooks\Service\Webhook;

use InvalidArgumentException;

/**
 * Class NotifierFactory
 */
class NotifierFactory implements NotifierFactoryInterface
{
    /**
     * @var array
     */
    private array $notifierClasses;

    /**
     * NotifierFactory constructor.
     *
     * @param array $notifierClasses
     */
    public function __construct(array $notifierClasses = [])
    {
        $this->notifierClasses = $notifierClasses;
    }

    /**
     * {@inheritDoc}
     */
    public function create(string $type): NotifierInterface
    {
        $notifier = $this->notifierClasses[$type] ?? null;

        if (!$notifier instanceof NotifierInterface) {
            throw new InvalidArgumentException(__("{$notifier} must implement NotifierInterface."));
        }

        return $notifier;
    }
}
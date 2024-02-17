<?php

/**
 * Swap out Config for TestConfig during queue consumer run so `example.event` is available in Integration tests.
 */

return [
    \MageOS\AsyncEvents\Model\Config::class => \MageOS\AsyncEvents\Test\Integration\TestConfig::class
];

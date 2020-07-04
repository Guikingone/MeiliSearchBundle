<?php

declare(strict_types=1);

namespace MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Exception\ExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof ExceptionInterface) {
            return;
        }

        $this->logger->critical(sprintf('[MeiliSearchBundle] An error occurred: %s', $exception->getMessage()), [
            'context' => $exception->getContext(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTrace(),
            'file' => $exception->getFile(),
        ]);
    }
}

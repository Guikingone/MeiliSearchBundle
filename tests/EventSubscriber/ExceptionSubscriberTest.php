<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\EventSubscriber;

use Exception;
use MeiliSearchBundle\EventSubscriber\ExceptionSubscriber;
use MeiliSearchBundle\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ExceptionSubscriberTest extends TestCase
{
    public function testSubscriberIsConfigured(): void
    {
        static::assertArrayHasKey(KernelEvents::EXCEPTION, ExceptionSubscriber::getSubscribedEvents());
    }

    public function testSubscriberCannotBeCalledOnInvalidException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');

        $kernel = $this->createMock(KernelInterface::class);
        $request = $this->createMock(Request::class);

        $event = new ExceptionEvent(
            $kernel,
            $request,
            KernelInterface::MASTER_REQUEST,
            new Exception('An error occurred')
        );

        $subscriber = new ExceptionSubscriber($logger);
        $subscriber->onException($event);
    }

    public function testSubscriberCannotBeCalledOnNullLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');

        $kernel = $this->createMock(KernelInterface::class);
        $request = $this->createMock(Request::class);

        $event = new ExceptionEvent(
            $kernel,
            $request,
            KernelInterface::MASTER_REQUEST,
            new Exception('An error occurred')
        );

        $subscriber = new ExceptionSubscriber();
        $subscriber->onException($event);
    }

    public function testSubscriberCanBeCalled(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('critical')->with(
            self::equalTo('[MeiliSearch] An error occurred: An error occurred'),
            [
                'error' => 'An error occurred',
            ]
        );

        $kernel = $this->createMock(KernelInterface::class);
        $request = $this->createMock(Request::class);

        $event = new ExceptionEvent(
            $kernel,
            $request,
            KernelInterface::MASTER_REQUEST,
            new InvalidArgumentException('An error occurred')
        );

        $subscriber = new ExceptionSubscriber($logger);
        $subscriber->onException($event);
    }
}

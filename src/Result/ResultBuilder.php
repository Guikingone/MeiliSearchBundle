<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Result;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use function array_key_exists;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ResultBuilder implements ResultBuilderInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SerializerInterface $serializer,
        ?LoggerInterface $logger = null
    ) {
        $this->serializer = $serializer;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function support(array $data): bool
    {
        return array_key_exists(ResultBuilderInterface::MODEL_KEY, $data);
    }

    public function build(array $data, array $buildContext = [])
    {
        try {
            return $this->serializer->denormalize($data, $data[self::MODEL_KEY], null, $buildContext);
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The data cannot be build, error: "%s"', $throwable->getMessage()));

            throw $throwable;
        }
    }
}

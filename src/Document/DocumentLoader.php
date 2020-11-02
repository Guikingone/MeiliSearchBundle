<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Document;

use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use MeiliSearchBundle\DataProvider\EmbeddedDocumentDataProviderInterface;
use MeiliSearchBundle\DataProvider\ModelDataProviderInterface;
use MeiliSearchBundle\DataProvider\PrimaryKeyOverrideDataProviderInterface;
use MeiliSearchBundle\DataProvider\PriorityDataProviderInterface;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Loader\LoaderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use function array_replace;
use function sprintf;
use function usort;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentLoader implements LoaderInterface
{
    /**
     * @var DocumentEntryPointInterface
     */
    private $orchestrator;

    /**
     * @var iterable|DocumentDataProviderInterface[]|EmbeddedDocumentDataProviderInterface[]|PrimaryKeyOverrideDataProviderInterface[]
     */
    private $documentProviders;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DocumentEntryPointInterface $orchestrator,
        iterable $documentProviders = [],
        ?LoggerInterface $logger = null
    ) {
        $this->orchestrator = $orchestrator;
        $this->documentProviders = $documentProviders;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function load(): void
    {
        if (empty($this->documentProviders)) {
            throw new RuntimeException('No providers found');
        }

        $providers = $this->filterOnPriority();

        foreach ($providers as $provider) {
            try {
                if ($provider instanceof EmbeddedDocumentDataProviderInterface) {
                    $this->orchestrator->addDocuments(
                        $provider->support(),
                        $provider->getDocument(),
                        $provider instanceof PrimaryKeyOverrideDataProviderInterface ? $provider->getPrimaryKey() : null
                    );

                    continue;
                }

                $this->orchestrator->addDocument(
                    $provider->support(),
                    $provider->getDocument(),
                    $provider instanceof PrimaryKeyOverrideDataProviderInterface ? $provider->getPrimaryKey() : null,
                    $provider instanceof ModelDataProviderInterface ? $provider->getModel() : null
                );
            } catch (Throwable $throwable) {
                $this->logger->error(sprintf(
                    'The document cannot be loaded, error: "%s"',
                    $throwable->getMessage()
                ));

                throw $throwable;
            }
        }
    }

    /**
     * @return array<int, DocumentDataProviderInterface>
     */
    private function filterOnPriority(): array
    {
        $defaultProviders = [];
        foreach ($this->documentProviders as $provider) {
            $defaultProviders[] = $provider;
        }

        $providers = [];
        foreach ($this->documentProviders as $provider) {
            if (!$provider instanceof PriorityDataProviderInterface) {
                continue;
            }

            $providers[] = $provider;
        }

        usort($providers, function (PriorityDataProviderInterface $provider, PriorityDataProviderInterface $nextProvider): bool {
            return $provider->getPriority() > $nextProvider->getPriority();
        });

        return array_replace($providers, $defaultProviders);
    }
}

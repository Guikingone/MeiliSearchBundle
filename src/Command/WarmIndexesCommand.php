<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class WarmIndexesCommand extends Command
{
    /**
     * @var array<string,array>
     */
    private $indexes;

    /**
     * @var IndexMetadataRegistry
     */
    private $indexMetadataRegistry;

    /**
     * @var IndexOrchestratorInterface
     */
    private $indexOrchestrator;

    /**
     * @var MessageBusInterface|null
     */
    private $messageBus;

    /**
     * @var string|null
     */
    private $prefix;

    protected static $defaultName = 'meili:warm-indexes';

    /**
     * @param array<string,array>        $indexes
     * @param IndexMetadataRegistry      $indexMetadataRegistry
     * @param IndexOrchestratorInterface $indexOrchestrator
     * @param MessageBusInterface|null   $messageBus
     * @param string|null                $indexPrefix
     */
    public function __construct(
        array $indexes,
        IndexMetadataRegistry $indexMetadataRegistry,
        IndexOrchestratorInterface $indexOrchestrator,
        ?MessageBusInterface $messageBus = null,
        ?string $indexPrefix = null
    ) {
        $this->indexes = $indexes;
        $this->indexMetadataRegistry = $indexMetadataRegistry;
        $this->indexOrchestrator = $indexOrchestrator;
        $this->messageBus = $messageBus;
        $this->prefix = $indexPrefix;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Allow to warm the indexes defined in the configuration')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (empty($this->indexes)) {
            $io->warning('No indexes found, please define at least a single index');

            return 0;
        }

        foreach ($this->indexes as $indexName => $configuration) {
            if ($configuration['async'] && null === $this->messageBus) {
                $io->error([
                    'The "async" attribute cannot be used when Messenger is not installed',
                    'Consider using "composer require symfony/messenger"'
                ]);
            }

            $indexName = null !== $this->prefix ? sprintf('%s_%s', $this->prefix, $indexName) : $indexName;

            $this->indexOrchestrator->addIndex($indexName, $configuration['primaryKey']);

            $this->indexMetadataRegistry->add($indexName, new IndexMetadata(
                $indexName,
                $configuration['async'],
                $configuration['primaryKey'],
                $configuration['rankingRules'],
                $configuration['stopWords'],
                $configuration['distinctAttribute'],
                $configuration['facetedAttributes'],
                $configuration['searchableAttributes'],
                $configuration['displayedAttributes']
            ));
        }

        $io->success('The indexes has been warmed, feel free to query them!');

        return 0;
    }
}

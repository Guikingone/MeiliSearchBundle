<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Document\DocumentMigrationOrchestratorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
#[AsCommand(
    name: 'meili:migrate-documents',
    description: 'Migrate the documents from an index to another one',
)]
final class MigrateDocumentsCommand extends Command
{
    public function __construct(private readonly DocumentMigrationOrchestratorInterface $migrationOrchestrator)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputArgument(
                    'oldIndex',
                    InputArgument::REQUIRED,
                    'The name of the index from the documents must be migrated'
                ),
                new InputOption(
                    'index',
                    'i',
                    InputOption::VALUE_REQUIRED,
                    'The name of the index where the documents must be migrated'
                ),
                new InputOption(
                    'remove',
                    'r',
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE,
                    'If the documents must be removed from the old index'
                ),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        try {
            $this->migrationOrchestrator->migrate(
                $input->getArgument('oldIndex'),
                $input->getOption('index'),
                $input->getOption('remove') ?? false
            );
        } catch (Throwable $throwable) {
            $style->error([
                'The documents cannot be migrated!',
                sprintf('Error: "%s"', $throwable->getMessage()),
            ]);

            return 1;
        }

        $style->success('The documents have been migrated');

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Client\IndexOrchestratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ListIndexesCommand extends Command
{
    /**
     * @var IndexOrchestratorInterface
     */
    private $indexOrchestrator;

    protected static $defaultName = 'meili:list-indexes';

    public function __construct(IndexOrchestratorInterface $indexOrchestrator)
    {
        $this->indexOrchestrator = $indexOrchestrator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $indexes = $this->indexOrchestrator->getIndexes();
            if (0 === \count($indexes)) {
                $io->warning('No indexes found, please ensure that indexes have been created');

                return 0;
            }

            $table = new Table($output);
            $table->setHeaders(['Uid', 'PrimaryKey', 'CreatedAt', 'UpdatedAt']);

            array_walk($indexes, function (array $index) use (&$table): void {
                $table->addRow([$index['uid'], $index['primaryKey'] ?? 'Undefined', $index['createdAt'], $index['updatedAt']]);
            });

            $io->note('The following indexes have been found:');
            $table->render();

            return 0;
        } catch (Throwable $exception) {
            $io->error(sprintf('The list cannot be retrieved as an error occurred, message: "%s".', $exception->getMessage()));

            return 1;
        }
    }
}

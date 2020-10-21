<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use function array_walk;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ListIndexesCommand extends Command
{
    /**
     * @var IndexOrchestratorInterface
     */
    private $indexOrchestrator;

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'meili:list-indexes';

    public function __construct(IndexOrchestratorInterface $indexOrchestrator)
    {
        $this->indexOrchestrator = $indexOrchestrator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('List the indexes')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $indexes = $this->indexOrchestrator->getIndexes();
            if (empty($indexes)) {
                $io->warning('No indexes found, please ensure that indexes have been created');

                return Command::SUCCESS;
            }

            $table = new Table($output);
            $table->setHeaders(['Uid', 'PrimaryKey', 'CreatedAt', 'UpdatedAt']);

            array_walk($indexes, function (Indexes $index) use (&$table): void {
                $informations = $index->show();

                $table->addRow([$informations['uid'], $informations['primaryKey'] ?? 'Undefined', $informations['createdAt'], $informations['updatedAt']]);
            });

            $io->note('The following indexes have been found:');
            $table->render();

            return Command::SUCCESS;
        } catch (Throwable $throwable) {
            $io->error([
                'The list cannot be retrieved as an error occurred',
                sprintf('Error: %s', $throwable->getMessage()),
            ]);

            return Command::FAILURE;
        }
    }
}

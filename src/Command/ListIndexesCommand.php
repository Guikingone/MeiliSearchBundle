<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
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
#[AsCommand(
    name: 'meili:list-indexes',
    description: 'List the indexes',
)]
final class ListIndexesCommand extends Command
{
    public function __construct(
        private readonly IndexOrchestratorInterface $indexOrchestrator
    ) {
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
            if (0 === $indexes->count()) {
                $io->warning('No indexes found, please ensure that indexes have been created');

                return 0;
            }

            $table = new Table($output);
            $table->setHeaders(['Uid', 'PrimaryKey', 'CreatedAt', 'UpdatedAt']);

            $indexes = $indexes->toArray();

            array_walk($indexes, static function (Indexes $index) use (&$table): void {
                /** @var array{uid:string, primaryKey:string|null, createdAt:string, updatedAt:string} $informations */
                $informations = $index->show();
                $table->addRow(
                    [
                        $informations['uid'],
                        $informations['primaryKey'] ?? 'Undefined',
                        $informations['createdAt'],
                        $informations['updatedAt'],
                    ]
                );
            });

            $io->note('The following indexes have been found:');
            $table->render();

            return 0;
        } catch (Throwable $throwable) {
            $io->error([
                'The list cannot be retrieved as an error occurred',
                sprintf('Error: %s', $throwable->getMessage()),
            ]);

            return 1;
        }
    }
}

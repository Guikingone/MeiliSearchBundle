<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexCommand extends Command
{
    private const INDEX = 'index';

    /**
     * @var IndexOrchestratorInterface
     */
    private $indexOrchestrator;

    protected static $defaultName = 'meili:delete-index';

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
            ->setDefinition([
                new InputArgument(self::INDEX, InputArgument::REQUIRED, 'The index to delete'),
            ])
            ->setDescription('Allow to delete an index')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $index = $input->getArgument(self::INDEX);

        try {
            $this->indexOrchestrator->removeIndex($index);
        } catch (Throwable $throwable) {
            $io->error([
                'An error occurred when trying to delete the index',
                sprintf('Error: %s', $throwable->getMessage()),
            ]);

            return 1;
        }

        $io->success(sprintf('The index "%s" has been deleted', $index));

        return 0;
    }
}

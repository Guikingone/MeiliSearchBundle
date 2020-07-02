<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Client\IndexOrchestratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexCommand extends Command
{
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
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('index', InputArgument::REQUIRED, 'The index to delete'),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $index = $input->getArgument('index');

        try {
            $this->indexOrchestrator->removeIndex($index);
        } catch (Throwable $exception) {
            $io->error(sprintf('An error occurred when trying to delete the index, error: "%s"', $exception->getMessage()));

            return 1;
        }

        $io->success(sprintf('The index "%s" has been deleted', $index));

        return 0;
    }
}

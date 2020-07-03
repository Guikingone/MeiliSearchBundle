<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Client\IndexOrchestratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class CreateIndexCommand extends Command
{
    /**
     * @var IndexOrchestratorInterface
     */
    private $indexOrchestrator;

    protected static $defaultName = 'meili:create-index';

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
                new InputArgument('uid', InputArgument::REQUIRED),
                new InputOption('primary_key', 'p', InputOption::VALUE_OPTIONAL, 'The primary_key of the index'),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $uid = $input->getArgument('uid');
        $primaryKey = $input->getOption('primary_key');

        try {
            $this->indexOrchestrator->addIndex($uid, $primaryKey);
        } catch (Throwable $exception) {
            $io->error(sprintf('The index cannot be created, error: "%s"', $exception->getMessage()));

            return 1;
        }

        $io->success(sprintf('The "%s" index has been created', $uid));

        return 0;
    }
}

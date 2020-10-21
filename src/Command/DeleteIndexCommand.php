<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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

    /**
     * {@inheritdoc}
     */
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
                new InputOption('force', 'f', InputOption::VALUE_OPTIONAL|InputOption::VALUE_NONE, 'Allow to force the deletion'),
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

        if ($io->askQuestion(new ConfirmationQuestion('Are you sure that you want to delete this index?', false)) || $input->getOption('force')) {
            $index = $input->getArgument(self::INDEX);

            try {
                $this->indexOrchestrator->removeIndex($index);
                $io->success(sprintf('The index "%s" has been removed', $index));

                return Command::SUCCESS;
            } catch (Throwable $throwable) {
                $io->error([
                    'An error occurred when trying to delete the index',
                    sprintf('Error: %s', $throwable->getMessage()),
                ]);

                return Command::FAILURE;
            }
        } else {
            $io->warning('The index has not been deleted');

            return Command::FAILURE;
        }
    }
}

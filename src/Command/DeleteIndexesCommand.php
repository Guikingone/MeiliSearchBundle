<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Index\IndexSynchronizerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexesCommand extends Command
{
    /**
     * @var IndexSynchronizerInterface
     */
    private $indexSynchronizer;

    /**
     * @var string|null
     */
    protected static $defaultName = 'meili:delete-indexes';

    public function __construct(IndexSynchronizerInterface $indexSynchronizer)
    {
        $this->indexSynchronizer = $indexSynchronizer;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Delete every indexes stored in MeiliSearch')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($io->askQuestion(new ConfirmationQuestion('Are you sure about this action?', false))) {
            try {
                $this->indexSynchronizer->dropIndexes();
            } catch (Throwable $throwable) {
                $io->error([
                    'An error occurred when trying to removed all the indexes',
                    sprintf('Error: "%s"', $throwable->getMessage()),
                ]);

                return 1;
            }

            $io->success('All the indexes have been removed');

            return 0;
        }

        $io->note('The action has been discarded');

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Index\IndexSynchronizerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
#[AsCommand(
    name: 'meili:update-indexes',
    description: 'Allow to update the indexes defined in the configuration',
)]
final class UpdateIndexesCommand extends Command
{
    /**
     * @param array<string, array> $indexes
     * @param string|null $prefix
     */
    public function __construct(
        private readonly array $indexes,
        private readonly IndexSynchronizerInterface $indexSynchronizer,
        private readonly ?string $prefix = null
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputOption(
                    'force',
                    'f',
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE,
                    'Force the action without asking for confirmation'
                ),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (empty($this->indexes)) {
            $io->warning('No indexes found, please define at least a single index');

            return 1;
        }

        if ($io->askQuestion(
            new ConfirmationQuestion('Are you sure that you want to update the indexes?', false)
        ) || $input->getOption('force')) {
            try {
                $this->indexSynchronizer->updateIndexes($this->indexes, $this->prefix);
            } catch (Throwable $throwable) {
                $io->error([
                    'The indexes cannot be updated!',
                    sprintf('Error: "%s"', $throwable->getMessage()),
                ]);

                return 1;
            }

            $io->success('The indexes has been updated, feel free to query them!');

            return 0;
        }
        $io->warning('The indexes update has been discarded');
        return 1;
    }
}

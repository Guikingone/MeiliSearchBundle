<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Index\IndexSynchronizerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class WarmIndexesCommand extends Command
{
    /**
     * @var array<string, array>
     */
    private $indexes;

    /**
     * @var IndexSynchronizerInterface
     */
    private $indexSynchronizer;

    /**
     * @var string|null
     */
    private $prefix;

    /**
     * @var string|null
     */
    protected static $defaultName = 'meili:warm-indexes';

    /**
     * @param array<string, array>       $indexes
     * @param IndexSynchronizerInterface $indexSynchronizer
     * @param string|null                $prefix
     */
    public function __construct(
        array $indexes,
        IndexSynchronizerInterface $indexSynchronizer,
        ?string $prefix = null
    ) {
        $this->indexes = $indexes;
        $this->indexSynchronizer = $indexSynchronizer;
        $this->prefix = $prefix;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Allow to warm the indexes defined in the configuration')
        ;
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

        try {
            $this->indexSynchronizer->createIndexes($this->indexes, $this->prefix);
        } catch (Throwable $throwable) {
            $io->error([
                'The indexes cannot be warmed!',
                sprintf('Error: "%s"', $throwable->getMessage())
            ]);

            return 1;
        }

        $io->success('The indexes has been warmed, feel free to query them!');

        return 0;
    }
}

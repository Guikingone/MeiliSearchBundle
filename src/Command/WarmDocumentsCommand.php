<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Loader\LoaderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class WarmDocumentsCommand extends Command
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    protected static $defaultName = 'meili:warm-documents';

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Warm the documents defined in DocumentDataProviders')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->loader->load();
        } catch (Throwable $throwable) {
            $io->error([
                'An error occurred during the documents warm process',
                sprintf('Error: %s', $throwable->getMessage()),
            ]);

            return 1;
        }

        $io->success('The documents have been imported, feel free to search them!');

        return 0;
    }
}

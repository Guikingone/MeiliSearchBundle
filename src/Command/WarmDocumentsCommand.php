<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Client\DocumentOrchestratorInterface;
use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
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
     * @var DocumentOrchestratorInterface
     */
    private $orchestrator;

    /**
     * @var iterable|DocumentDataProviderInterface[]
     */
    private $dataProviders;

    protected static $defaultName = 'meili:warm-documents';

    public function __construct(DocumentOrchestratorInterface $orchestrator, iterable $dataProviders = [])
    {
        $this->orchestrator = $orchestrator;
        $this->dataProviders = $dataProviders;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('index', InputArgument::REQUIRED, 'The index to warm'),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (0 === \count($this->dataProviders)) {
            $io->warning('No providers found, please be sure that you define at least a single provider');

            return 0;
        }

        $index = $input->getArgument('index');
        $io->note(sprintf('Currently loading the documents for the "%s" index', $index));

        $progressBar = new ProgressBar($output, \count($this->dataProviders));
        $progressBar->start();

        foreach ($this->dataProviders as $provider) {
            if ($index !== $provider->support()) {
                continue;
            }

            try {
                $this->orchestrator->addDocument($index, $provider->getDocument(), $provider->getPrimaryKey());
            } catch (Throwable $exception) {
                $io->error(sprintf('An error occurred when warming the documents, error: "%s"', $exception->getMessage()));

                return 1;
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $io->success('The documents have been imported, feel free to search them!');

        return 0;
    }
}

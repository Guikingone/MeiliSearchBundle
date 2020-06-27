<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Client\DocumentOrchestrator;
use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class WarmDocumentsCommand extends Command
{
    /**
     * @var DocumentOrchestrator
     */
    private $orchestrator;

    /**
     * @var iterable|DocumentDataProviderInterface[]
     */
    private $dataProviders;

    protected static $defaultName = 'meili:warm';

    public function __construct(DocumentOrchestrator $orchestrator, iterable $dataProviders = [])
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
                new InputArgument('index',InputArgument::REQUIRED),
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

        foreach ($this->dataProviders as $provider) {
            if ($index !== $provider->support()) {
                continue;
            }

            if ($provider instanceof DocumentDataProviderInterface) {
                $this->orchestrator->addDocument($index, $provider->getDocument(), $provider->getPrimaryKey());
            }
        }

        $io->success('The document have been imported, feel free to search them!');

        return 0;
    }
}

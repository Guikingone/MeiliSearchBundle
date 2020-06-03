<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Client\DocumentOrchestrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class WarmDocumentsCommand extends Command
{
    /**
     * @var DocumentOrchestrator
     */
    private $orchestrator;

    protected static $defaultName = 'meili:warm';

    public function __construct(DocumentOrchestrator $orchestrator)
    {
        $this->orchestrator = $orchestrator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputOption('directory', 'd', InputOption::VALUE_REQUIRED),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}

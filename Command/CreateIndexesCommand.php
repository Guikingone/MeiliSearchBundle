<?php

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Client\ClientInterface;
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
final class CreateIndexesCommand extends Command
{
    /**
     * @var ClientInterface
     */
    private $client;

    protected static $defaultName = 'meili:create-indexes';

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputArgument('index', InputArgument::REQUIRED),
                new InputOption('uid', 'u', InputOption::VALUE_OPTIONAL, 'The uid of the index'),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $index = $input->getArgument('index');
        $uid = $input->getOption('uid');

        try {
            $this->client->createIndex($index, $uid);
        } catch (Throwable $exception) {
            $io->error(sprintf('The index cannot be created, error: "%s"', $exception->getMessage()));

            return 1;
        }

        $io->success(sprintf('The "%s" index has been created', $index));

        return 0;
    }
}

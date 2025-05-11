<?php
namespace App\Command;

use App\Service\MessageProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessMessagesCommand extends Command
{
    protected static $defaultName = 'process:messages';
    private MessageProcessor $processor;

    public function __construct(MessageProcessor $processor)
    {
        parent::__construct();
        $this->processor = $processor;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Process input JSON messages into reviews and incidents')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to recruitment-task-source.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        $io->title('Message Processing Started');
        $results = $this->processor->process($file);

        $outputDir = 'output';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        file_put_contents("$outputDir/maintenance.json", json_encode(array_map(fn($r) => $r->toArray(), $results['reviews']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents("$outputDir/malfunctions.json", json_encode(array_map(fn($i) => $i->toArray(), $results['incidents']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents("$outputDir/failures.json", json_encode(array_map(fn($f) => $f->toArray(), $results['failures']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $io->success(
            sprintf(
                'Processed %d messages: %d maintenances %d malfunctions, %d failures.',
                $results['total'],
                count($results['reviews']),
                count($results['incidents']),
                count($results['failures'])
            )
        );

        return Command::SUCCESS;
    }
}

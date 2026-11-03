<?php declare(strict_types=1);

namespace Dravencms\Locale\Console;

use Dravencms\Locale\Setup\DefaultDataInstaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;

final class SetupCommand extends Command
{
    public function __construct(private DefaultDataInstaller $installer) { parent::__construct('locale:setup'); }

    protected function configure(): void
    {
        $this->setDescription('Add missing locale defaults without replacing existing data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->installer->install();
        $output->writeln('Missing locale defaults installed; existing records retained.');
        return Command::SUCCESS;
    }
}

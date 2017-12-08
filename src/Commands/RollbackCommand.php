<?php

namespace Rougin\Refinery\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Rollback Command
 *
 * Returns to a previous or specified migration.
 *
 * @package Refinery
 * @author  Rougin Royce Gutib <rougingutib@gmail.com>
 */
class RollbackCommand extends AbstractCommand
{
    /**
     * Sets the configurations of the specified command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('rollback')
            ->setDescription('Returns to a previous or specified migration')
            ->addArgument('version', InputArgument::OPTIONAL, 'Specified version of the migration')
            ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Name of the "DIRECTORY" to have migration', 'varchar');
    }

    /**
     * Executes the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return object|\Symfony\Component\Console\Output\OutputInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($filenames, $migrations) = $this->getMigrations(APPPATH . 'migrations' . (($input->getOption('directory') !== NULL) ? $input->getOption('directory') : ''));

        $current = $this->getLatestVersion(true);
        $version = $input->getArgument('version');

        $this->isValidVersion($migrations, $version);

        $migration = $version ?: $migrations[count($migrations) - 1];
        $fileName  = $filenames[count($migrations) - 1];

        $this->migrate($current, $migration);

        $message = "Database is reverted back to version $migration ($fileName)";

        return $output->writeln('<info>' . $message . '</info>');
    }

    /**
     * Checks if the version is valid to be rolled back.
     *
     * @param  array   $migrations
     * @param  string  $version
     * @return boolean
     */
    protected function isValidVersion(array $migrations, $version)
    {
        foreach ($migrations as $migration) {
            if ($migration == $version || empty($version)) {
                return true;
            }
        }

        throw new \InvalidArgumentException('Cannot rollback to version ' . $version);
    }
}

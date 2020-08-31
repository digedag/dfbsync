<?php
namespace System25\T3sports\DfbSync\Command;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

use TYPO3\CMS\Core\Core\Bootstrap;

use System25\T3sports\DfbSync\Sync\Runner;

/**
 * Core function for unlocking the TYPO3 Backend
 */
class SyncCommand extends Command
{

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Sync competition with DFB data source')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Search path for XML files')
            ->addOption('saison', 's', InputOption::VALUE_REQUIRED, 'UID of current saison');
    }

    /**
     * Executes the command for removing the lock file
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Make sure the _cli_ user is loaded
        Bootstrap::initializeBackendAuthentication();

        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $path = $input->getOption('path');
        $saisonUid = (int) $input->getOption('saison');

        $isAbs = \tx_rnbase_util_Files::isAbsPath($path);
        $io->note('Path: '. $path . ': '.($isAbs ? 'Abs' : 'rel'));
        $io->note('Saison: '. $saisonUid);
        $io->note('Path-site: ' . PATH_site);

        $runner = new Runner();
        $info = $runner->sync($saisonUid, $path);
        $io->note(print_r($info, true));
        $io->success('Done');
    }
}

<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Elements\Bundle\ExportToolkitBundle\Command;

use Elements\Bundle\ExportToolkitBundle\ExportService;
use Elements\Bundle\ExportToolkitBundle\Helper;
use Elements\Bundle\ProcessManagerBundle\ExecutionTrait;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

class ExportCommand extends AbstractCommand
{
    use ExecutionTrait;

    protected LockFactory $lockFactory;

    protected ExportService $exportService;

    public function __construct(LockFactory $lockFactory, ExportService $exportService, string $name = null)
    {
        parent::__construct($name);
        $this->lockFactory = $lockFactory;
        $this->exportService = $exportService;
    }

    protected function configure()
    {
        $this
            ->setName('export-toolkit:export')
            ->setDescription('Executes a specific export toolkit configuration')
            ->addOption(
                'config-name', null,
                InputOption::VALUE_REQUIRED,
                'the name of the configuration which should be used'
            )
            ->addOption(
                'monitoring-item-id', null,
                InputOption::VALUE_REQUIRED,
                'Contains the monitoring item if executed via the Pimcore backend'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $monitoringItemId = $input->getOption('monitoring-item-id');
        if (!$monitoringItemId) { //executed directly from export toolkit
            $lock = Helper::getLock($this->lockFactory, $input->getOption('config-name'));
            $lock->acquire(true);
        }

        $this->initProcessManager($input->getOption('monitoring-item-id'), ['autoCreate' => true, 'name' => $input->getOption('config-name')]);
        $this->exportService->executeExport($input->getOption('config-name'));

        if (!$monitoringItemId) {
            $lock->release();
        }
        return 0;
    }
}

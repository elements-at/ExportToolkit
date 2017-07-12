<?php

namespace ExportToolkit;

use ExportToolkit\ExportService\Worker;
use Pimcore\Log\Simple;
use Pimcore\Model\Object\AbstractObject;

class ExportService {
    use \ProcessManager\ExecutionTrait;

    /**
     * @var Worker[]
     */
    protected $workers;

    public function __construct() {

        $exporters = Configuration\Dao::getList();
        $this->workers = array();
        foreach($exporters as $exporter) {
            $this->workers[$exporter->getName()] = new Worker($exporter);
        }

    }

    public function setUpExport($objectHook = false, $hookType = "save") {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, $hookType)) {
                $worker->setUpExport();
            }
        }
    }

    public function deleteFromExport(AbstractObject $object, $objectHook = false) {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, "delete")) {
                if($worker->checkClass($object)) {
                    $worker->deleteFromExport($object);
                } else {
                    \Logger::info("do not delete from export - object " . $object->getId() . " for " . $workerName . ".");
                }
            }
        }
    }

    public function updateExport(AbstractObject $object, $objectHook = false, $hookType = "save") {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, $hookType)) {
                if($worker->checkClass($object)) {
                    $worker->updateExport($object);
                } else {
                    \Logger::info("do not update export object " . $object->getId() . " for " . $workerName . ".");
                }
            }
        }
    }

    public function commitData($objectHook = false, $hookType = "save") {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, $hookType)) {
                $worker->commitData();
            }
        }
    }

    public function executeExport($workerName = null) {

        if($workerName) {
            $worker = $this->workers[$workerName];
            $this->doExecuteExport($worker, $workerName);
        } else {
            foreach($this->workers as $workerName => $worker) {
                $this->doExecuteExport($worker, $workerName);
            }
        }

    }

    protected function doExecuteExport(Worker $worker, $workerName) {

        $this->initProcessManager(null, ["name" => $workerName, "autoCreate" => true]);

        $monitoringItem = $this->getMonitoringItem();
        $monitoringItem->getLogger()->info("export-toolkit-" . $workerName);

        $worker->setLogger($monitoringItem->getLogger());

        Simple::log("export-toolkit-" . $workerName, "");

        //step 1 - setting up export
        $monitoringItem->setTotalSteps(3)->setCurrentStep(1)->setMessage("Setting up export $workerName")->save();

        $limit = (int)$worker->getWorkerConfig()->getConfiguration()->general->limit;

        $page = $i = 0;
        $pageSize = 100;
        $count = $pageSize;

        $totalObjectCount = $worker->getObjectList()->count();
        if ($pageSize > $totalObjectCount) {
            $pageSize = $totalObjectCount;
        }

        $worker->setUpExport(false);


        //step 2 - exporting data
        $monitoringItem->setCurrentStep(2)->setMessage("Starting Exporting Data")->setTotalWorkload($totalObjectCount)->save();

        while($count > 0) {
            Simple::log("export-toolkit-" . $workerName, "=========================");
            Simple::log("export-toolkit-" . $workerName, "Page $workerName: $page");
            Simple::log("export-toolkit-" . $workerName, "=========================");

            $objects = $worker->getObjectList();
            $offset = $page * $pageSize;
            $objects->setOffset($offset);
            $objects->setLimit($pageSize);

            $items = $objects->load();
            $monitoringItem->setCurrentWorkload(($offset) ?:1 )->setDefaultProcessMessage($items[0] ? $items[0]->getClassName() : 'Items')->save();
            foreach($items as $object) {
                Simple::log("export-toolkit-" . $workerName, "Updating object " . $object->getId());

                $monitoringItem->getLogger()->debug("Updating object " . $object->getId());

                if($worker->checkClass($object)) {
                    $worker->updateExport($object);
                } else {
                    $monitoringItem->getLogger()->debug("do not update export object " . $object->getId() . " for " . $workerName . ".");
                    Simple::log("export-toolkit-" . $workerName, "do not update export object " . $object->getId() . " for " . $workerName . ".");
                }
                $i++;
                if($limit && ($i == $limit)){
                    break 2;
                }
            }
            $page++;
            $count = count($objects->getObjects());

            $monitoringItem->setCurrentWorkload($page * $pageSize)->setTotalWorkload($totalObjectCount)->save();
            $monitoringItem->getLogger()->info("Process Export $workerName, finished page: $page");

            \Pimcore::collectGarbage();
        }

        $monitoringItem->setWorloadCompleted()->save();


        //step 3 - committing data
        $monitoringItem->setCurrentStep(3)->setMessage("Committing Data")->save();

        $worker->commitData();

        $monitoringItem->setMessage('Job finished')->setCompleted();
    }
}


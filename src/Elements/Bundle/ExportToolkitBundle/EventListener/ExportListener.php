<?php

namespace Elements\Bundle\ExportToolkitBundle\EventListener;


use Elements\Bundle\ExportToolkitBundle\ExportService;
use Pimcore\Event\Model\ObjectEvent;
use Pimcore\Event\ObjectEvents;
use Pimcore\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExportListener implements EventSubscriberInterface
{

    protected $exportService;

    public function  __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function postAddObject(ObjectEvent $event) {
        try {
            $object = $event->getObject();
            $this->exportService->setUpExport(true);
            $this->exportService->updateExport($object, true);
            $this->exportService->commitData(true);
        } catch (\Exception $e) {
            Logger::error($e);
        }

    }

    public function postUpdateObject(ObjectEvent $event) {
        try {
            $object = $event->getObject();
            $this->exportService->setUpExport(true);
            $this->exportService->updateExport($object, true);
            $this->exportService->commitData(true);
        } catch (\Exception $e) {
            Logger::error($e);
        }
    }


    public function postDeleteObject(ObjectEvent $event) {
        try {
            $object = $event->getObject();$object = $event->getObject();
            $this->exportService->setUpExport(true, "delete");
            $this->exportService->deleteFromExport($object, true);
            $this->exportService->commitData(true, "delete");
        } catch (\Exception $e) {
            Logger::error($e);
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            ObjectEvents::POST_UPDATE => array("postUpdateObject", 100),
            ObjectEvents::POST_DELETE => "postDeleteObject",
            ObjectEvents::POST_ADD => "postAddObject"

        );
    }

}

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

namespace Elements\Bundle\ExportToolkitBundle\EventListener;

use Elements\Bundle\ExportToolkitBundle\ExportService;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExportListener implements EventSubscriberInterface
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function postAddObject(DataObjectEvent $event)
    {
        try {
            $object = $event->getObject();
            $this->exportService->setUpExport(true);
            $this->exportService->updateExport($object, true);
            $this->exportService->commitData(true);
        } catch (\Exception $e) {
            Logger::error($e);
        }
    }

    public function postUpdateObject(DataObjectEvent $event)
    {
        try {
            $object = $event->getObject();
            $this->exportService->setUpExport(true);
            $this->exportService->updateExport($object, true);
            $this->exportService->commitData(true);
        } catch (\Exception $e) {
            Logger::error($e);
        }
    }

    public function postDeleteObject(DataObjectEvent $event)
    {
        try {
            $object = $event->getObject();
            $object = $event->getObject();
            $this->exportService->setUpExport(true, 'delete');
            $this->exportService->deleteFromExport($object, true);
            $this->exportService->commitData(true, 'delete');
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
        return [
            DataObjectEvents::POST_UPDATE => ['postUpdateObject', 100],
            DataObjectEvents::POST_DELETE => 'postDeleteObject',
            DataObjectEvents::POST_ADD => 'postAddObject'
        ];
    }
}

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

namespace Elements\Bundle\ExportToolkitBundle\ExportService\AttributeClusterInterpreter;

use Elements\Bundle\ExportToolkitBundle\Traits\LoggerAwareTrait;
use Pimcore\Model\DataObject\AbstractObject;
use Psr\Log\NullLogger;

abstract class AbstractAttributeClusterInterpreter
{
    use LoggerAwareTrait;

    protected $data;

    public function __construct($config)
    {
        $this->data = [];
        $this->config = $config;

        // add a default logger implementation so we can rely on a logger being set
        $this->logger = new NullLogger();
    }

    /**
     * This method is executed before the export is launched.
     * For example it can be used to clean up old export files, start a database transaction, etc.
     * If not needed, just leave the method empty.
     *
     */
    abstract public function setUpExport();

    /**
     * used internally to set data to the data array
     *
     * @param AbstractObject $object
     * @param $key
     * @param $value
     */
    public function setData(AbstractObject $object, $key, $value)
    {
        $rowData = $this->data[$object->getId()]?? [];
        $rowData[$key] = $value;
        $this->data[$object->getId()] = $rowData;
    }

    /**
     * This method is executed after all defined attributes of an object are exported.
     * The to-export data is stored in the array $this->data[OBJECT_ID].
     * For example it can be used to write each exported row to a destination database,
     * write the exported entries to a file, etc.
     * If not needed, just leave the method empty.
     *
     * @param AbstractObject $object
     */
    abstract public function commitDataRow(AbstractObject $object);

    /**
     * This method is executed after all objects are exported.
     * If not cleaned up in the commitDataRow-method, all exported data is stored in the array $this->data.
     * For example it can be used to write all data to a xml file or commit a database transaction, etc.
     *
     */
    abstract public function commitData();

    /**
     * This method is executed of an object is not exported (anymore).
     * For example it can be used to remove the entries from a destination database, etc.
     *
     * @param AbstractObject $object
     */
    abstract public function deleteFromExport(AbstractObject $object);

    /** Override point
     * @param AbstractObject $object
     *
     * @return bool return true if interpreter wants to consume the object
     */
    public function isRelevant(AbstractObject $object)
    {
        return true;
    }
}

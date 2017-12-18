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
 *  @copyright  Copyright (c) elements.at New Media Solutions GmbH (http://www.elements.at)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace ExportToolkit\ExportService\AttributeClusterInterpreter;

use Pimcore\Model\Object\AbstractObject;

class DefaultJson extends AbstractAttributeClusterInterpreter
{
    protected $firstData = true;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->firstData = true;
    }

    protected function getExportFile()
    {
        return PIMCORE_DOCUMENT_ROOT . '/' . ($this->config->filename ? $this->config->filename : 'website/var/plugins/ExportToolkit/export.json');
    }

    public function commitDataRow(AbstractObject $object)
    {
    }

    public function commitData()
    {
        file_put_contents($this->getExportFile(), json_encode(array_values($this->data), JSON_PRETTY_PRINT));
    }

    public function deleteFromExport(AbstractObject $object)
    {
        // nothing to do here
    }

    public function setUpExport()
    {
        //nothing to do here
    }
}

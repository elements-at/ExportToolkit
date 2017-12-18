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

class DefaultCsv extends AbstractAttributeClusterInterpreter
{
    protected $firstData = true;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->firstData = true;
    }

    protected function getExportFile()
    {
        $file = PIMCORE_DOCUMENT_ROOT . '/' . ($this->config->filename ? $this->config->filename : 'website/var/plugins/ExportToolkit/export.csv');
        $dir = dirname($file);
        if (!is_dir($dir)) {
            \Pimcore\File::mkdir($dir);
        }

        return $file;
    }

    protected function doCommitData()
    {
        if ($this->config->filename && $this->data) {
            $fp = fopen($this->getExportFile(), 'a');

            $firstRow = reset($this->data);

            if ($this->firstData) {
                fputcsv($fp, array_keys($firstRow));
                $this->firstData = false;
            }

            foreach ($this->data as $row) {
                fputcsv($fp, array_values($row));
            }

            fclose($fp);
        }
        $this->data = [];
    }

    public function commitDataRow(AbstractObject $object)
    {
        if (count($this->data) > 500) {
            $this->doCommitData();
        }
    }

    public function commitData()
    {
        $this->doCommitData();
    }

    public function deleteFromExport(AbstractObject $object)
    {
        // nothing to do here
    }

    public function setUpExport()
    {
        if ($this->config->filename && $this->config->deleteFile) {
            @unlink($this->getExportFile());
        }
    }
}

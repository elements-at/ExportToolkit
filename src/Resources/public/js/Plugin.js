/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @category   Pimcore
 * @package    EcommerceFramework
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */


pimcore.registerNS("pimcore.plugin.outputDataConfigToolkit.Plugin");

pimcore.plugin.outputDataConfigToolkit.Plugin = Class.create({


    getClassName: function () {
        return "pimcore.plugin.outputDataConfigToolkit.Plugin";
    },

    initialize: function() {
    },


    uninstall: function() {
        
    }
});

new pimcore.plugin.outputDataConfigToolkit.Plugin();

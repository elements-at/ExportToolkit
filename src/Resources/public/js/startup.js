pimcore.registerNS("pimcore.plugin.ExportToolkit");

pimcore.plugin.ExportToolkit = Class.create({
    getClassName: function() {
        return "pimcore.plugin.ExportToolkit";
    },

    initialize: function() {
        if (pimcore.events.preMenuBuild) {
            document.addEventListener(pimcore.events.preMenuBuild, this.preMenuBuild.bind(this));
        }

    },

    preMenuBuild: function (e) {
        this.items = [];

        this.initMenu();

        const user = pimcore.globalmanager.get("user");

        if (pimcore.globalmanager.get("user").isAllowed("plugin_exporttoolkit_config")) {
            let menu = e.detail.menu;

            menu.exportToolkitMenu = {
                label: t('plugin_exporttoolkit_configpanel'),
                iconCls: 'pimcore_icon_arrow_right',
                priority: 45,
                items: this.items,
                shadow: false,
                cls: "pimcore_navigation_flyout"
            };

            var menuOptions = {
                cls: "pimcore_navigation_flyout",
                shadow: false,
                items: []
            };
        }
        
        return null;
    },

    initMenu: function() {
        // add to menu
        let cacheClearMenuItem =  {
            text: t('plugin_exporttoolkit_clear_config_cache'),
            iconCls: 'plugin_exporttoolkit_clear_config_cache',
            hideOnClick: true,
            menu: null,
            handler: function () {
                Ext.Ajax.request({
                    url: '/admin/elementsexporttoolkit/config/clear-cache'
                });
            }
        };

        let configMenuItem =  {
            text: t('plugin_exporttoolkit_configpanel'),
            iconCls: 'plugin_exporttoolkit_configpanel',
            hideOnClick: true,
            menu: null,
            handler: function () {
                try {
                    pimcore.globalmanager.get("plugin_exporttoolkit_configpanel").activate();
                }
                catch (e) {
                    //console.log(e);
                    pimcore.globalmanager.add("plugin_exporttoolkit_configpanel", new pimcore.plugin.exporttoolkit.config.ConfigPanel());
                }
            }
        };

        this.items.push(cacheClearMenuItem);
        this.items.push(configMenuItem);

    },
});

var exporttoolkitPlugin = new pimcore.plugin.ExportToolkit();


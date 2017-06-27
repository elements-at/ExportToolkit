pimcore.registerNS("pimcore.plugin.ExportToolkit");

pimcore.plugin.ExportToolkit = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.ExportToolkit";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){
        if(pimcore.globalmanager.get("user").isAllowed("plugin_exporttoolkit_config")) {

            var toolbar = pimcore.globalmanager.get("layout_toolbar");
            var user = pimcore.globalmanager.get("user");
            var searchButton = Ext.get("pimcore_menu_settings");

            // init
            var menuItems = toolbar.exportToolkitMenu;
            if(!menuItems) {
                menuItems = new Ext.menu.Menu({cls: "pimcore_navigation_flyout"});
                toolbar.exportToolkitMenu = menuItems;
            }

            menuItems.add({
                text: t("plugin_exporttoolkit_clear_config_cache"),
                iconCls: "plugin_exporttoolkit_clear_config_cache",
                handler: function () {
                    Ext.Ajax.request({
                        url: '/admin/elementsexporttoolkit/config/clear-cache'
                    });
                }
            });

            menuItems.add({
                text: t("plugin_exporttoolkit_configpanel"),
                iconCls: "plugin_exporttoolkit_configpanel",
                handler: function () {
                    try {
                        pimcore.globalmanager.get("plugin_exporttoolkit_configpanel").activate();
                    }
                    catch (e) {
                        //console.log(e);
                        pimcore.globalmanager.add("plugin_exporttoolkit_configpanel", new pimcore.plugin.exporttoolkit.config.ConfigPanel());
                    }
                }
            });

            if(menuItems.items.length > 0)
            {
                this.navEl = Ext.get(
                    searchButton.insertHtml(
                        "afterEnd",
                        '<li id="pimcore_menu_exporttoolkit" data-menu-tooltip="Export Toolkit" class="pimcore_menu_item icon-upload">' + t('plugin_exporttoolkit_mainmenu') + '</li>'
                    )
                );

                this.navEl.on("mousedown", toolbar.showSubMenu.bind(menuItems));
                pimcore.helpers.initMenuTooltips();
            }

        }

    }
});

var exporttoolkitPlugin = new pimcore.plugin.ExportToolkit();


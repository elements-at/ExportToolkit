var ExportToolkit = pimcore.registerNS("pimcore.plugin.ExportToolkit.Plugin");

ExportToolkit.config = {
    PLUGIN_PANEL_KEY: 'plugin_exporttoolkit_panel',
    ITEM_PANEL_PREFIX: 'plugin_exporttoolkit_itempanel_',
    TreeType: {
        FOLDER: 'folder',
        CONFIGURATION: 'config'
    },
    NAME_REGEX: /[a-zA-Z0-9_\-]+/,
    CANCEL: 'cancel',
    OK: 'ok',
    YES: 'yes'
};

ExportToolkit.Plugin = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.ExportToolkit";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        if (pimcore.globalmanager.get("user").isAllowed("plugin_exporttoolkit_config")) {
            pimcore.globalmanager.get('layout_toolbar').settingsMenu.add({
                text: t("plugin_exporttoolkit_configpanel"),
                iconCls: "pimcore_icon_custom_views",
                handler: function () {
                    var panel = pimcore.globalmanager.get(ExportToolkit.config.PLUGIN_PANEL_KEY);

                    if(panel) {
                        panel.activate();
                    } else {
                        pimcore.globalmanager.add(ExportToolkit.config.PLUGIN_PANEL_KEY, new ExportToolkit.ConfigPanel());
                    }
                }
            });
        }
    }
});

new ExportToolkit.Plugin();


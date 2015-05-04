pimcore.registerNS("pimcore.plugin.exporttoolkit.config.ConfigPanel");
pimcore.plugin.exporttoolkit.config.ConfigPanel = Class.create({

    initialize: function () {

        this.getTabPanel();
    },

    activate: function () {
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.activate("plugin_exporttoolkit_configpanel");
    },

    getTabPanel: function () {

        if (!this.panel) {
            this.panel = new Ext.Panel({
                id: "plugin_exporttoolkit_configpanel",
                title: t("plugin_exporttoolkit_configpanel"),
                iconCls: "plugin_exporttoolkit_configpanel",
                border: false,
                layout: "border",
                closable:true,
                items: [this.getTree(), this.getEditPanel()]
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.activate("plugin_exporttoolkit_configpanel");

            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("plugin_exporttoolkit_configpanel");
            }.bind(this));

            pimcore.layout.refresh();
        }

        return this.panel;
    },

    getTree: function () {
        if (!this.tree) {
            this.tree = new Ext.tree.TreePanel({
                id: "plugin_exporttoolkit_configpanel_tree",
                region: "west",
                useArrows:true,
                autoScroll:true,
                animate:true,
                containerScroll: true,
                border: true,
                width: 200,
                split: true,
                root: {
                    nodeType: 'async',
                    id: '0'
                },
                loader: new Ext.tree.TreeLoader({
                    dataUrl: '/plugin/ExportToolkit/config/list',
                    requestMethod: "GET",
                    baseAttrs: {
                        listeners: this.getTreeNodeListeners(),
                        reference: this,
                        allowDrop: false,
                        allowChildren: false,
                        isTarget: false,
                        iconCls: "plugin_exporttoolkit_config",
                        leaf: true
                    }
                }),
                rootVisible: false,
                tbar: {
                    items: [
                        {
                            text: t("plugin_exporttoolkit_configpanel_add"),
                            iconCls: "pimcore_icon_add",
                            handler: this.addField.bind(this)
                        }
                    ]
                }
            });

            this.tree.on("render", function () {
                this.getRootNode().expand();
            });
        }

        return this.tree;
    },

    getEditPanel: function () {
        if (!this.editPanel) {
            this.editPanel = new Ext.TabPanel({
                region: "center"
            });
        }

        return this.editPanel;
    },

    getTreeNodeListeners: function () {
        var treeNodeListeners = {
            'click' : this.onTreeNodeClick.bind(this),
            "contextmenu": this.onTreeNodeContextmenu
        };

        return treeNodeListeners;
    },

    onTreeNodeClick: function (node) {
        this.openConfig(node.id);
    },

    openConfig: function(id) {
        var existingPanel = Ext.getCmp("plugin_exporttoolkit_configpanel_panel_" + id);
        if(existingPanel) {
            this.editPanel.activate(existingPanel);
            return;
        }

        Ext.Ajax.request({
            url: "/plugin/ExportToolkit/config/get",
            params: {
                name: id
            },
            success: function (response) {
                var data = Ext.decode(response.responseText);

                var fieldPanel = new pimcore.plugin.exporttoolkit.config.Item(data, this);
                pimcore.layout.refresh();
            }.bind(this)
        });
    },

    onTreeNodeContextmenu: function () {
        this.select();

        var menu = new Ext.menu.Menu();
        menu.add(new Ext.menu.Item({
            text: t('delete'),
            iconCls: "pimcore_icon_delete",
            handler: this.attributes.reference.deleteField.bind(this)
        }));

        menu.show(this.ui.getAnchor());
    },

    addField: function () {
        Ext.MessageBox.prompt(t('plugin_exporttoolkit_configpanel_enterkey_title'), t('plugin_exporttoolkit_configpanel_enterkey_prompt'), this.addFieldComplete.bind(this), null, null, "");
    },

    addFieldComplete: function (button, value, object) {

        var regresult = value.match(/[a-zA-Z0-9_\-]+/);
        if (button == "ok" && value.length > 2 && regresult == value) {
            Ext.Ajax.request({
                url: "/plugin/ExportToolkit/config/add",
                params: {
                    name: value
                },
                success: function (response) {
                    var data = Ext.decode(response.responseText);

                    this.tree.getRootNode().reload();

                    if(!data || !data.success) {
                        pimcore.helpers.showNotification(t("error"), t("plugin_exporttoolkit_configpanel_error_adding_config"), "error", data.message);
                    } else {
                        this.openConfig(data.name);
                    }

                }.bind(this)
            });
        }
        else if (button == "cancel") {
            return;
        }
        else {
            Ext.Msg.alert(t("plugin_exporttoolkit_configpanel"), t("plugin_exporttoolkit_configpanel_invalid_name"));
        }
    },

    deleteField: function () {
        Ext.Msg.confirm(t('delete'), t('delete_message'), function(btn){
            if (btn == 'yes'){
                Ext.Ajax.request({
                    url: "/plugin/ExportToolkit/config/delete",
                    params: {
                        name: this.id
                    }
                });

                this.attributes.reference.getEditPanel().removeAll();
                this.remove();
            }
        }.bind(this));
    }
});


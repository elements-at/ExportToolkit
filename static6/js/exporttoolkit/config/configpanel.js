var ExportToolkit = pimcore.registerNS("pimcore.plugin.ExportToolkit.ConfigPanel");

ExportToolkit.ConfigPanel = Class.create({
    initialize: function () {
        this.getTabPanel();
    },

    activate: function () {
        Ext.getCmp("pimcore_panel_tabs").setActiveItem(this.getTabPanel());
    },

    getTabPanel: function () {
        if (!this.panel) {
            this.panel = new Ext.Panel({
                title: t("plugin_exporttoolkit_configpanel"),
                iconCls: "pimcore_icon_custom_views",
                border: false,
                layout: "border",
                closable: true,
                items: [
                    this.getTree(),
                    this.getEditPanel()
                ]
            });

            Ext.getCmp("pimcore_panel_tabs").add(this.panel);
            this.activate();

            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove(ExportToolkit.config.PLUGIN_PANEL_KEY);
            }.bind(this));

            pimcore.layout.refresh();
        }

        return this.panel;
    },

    getTree: function () {
        if (!this.tree) {
            var store = Ext.create('Ext.data.TreeStore', {
                proxy: {
                    type: 'ajax',
                    url: '/plugin/ExportToolkit/config/list',
                    reader: {
                        type: 'json'
                    }
                },
                root: {
                    text: t("plugin_exporttoolkit_configpanel"),
                    iconCls: 'pimcore_icon_folder',
                    expanded: true,
                    type: ExportToolkit.config.TreeType.FOLDER
                }
            });

            this.tree = Ext.create('Ext.tree.Panel', {
                store: store,
                region: "west",
                scrollable: true,
                border: true,
                width: 300,
                rootVisible: true,
                split: true,
                viewConfig: {
                    xtype: 'pimcoretreeview',
                    plugins: {
                        ptype: 'treeviewdragdrop',
                        appendOnly: false,
                        ddGroup: 'elements',
                        scrollable: true
                    },
                    listeners: {
                        nodedragover: this.onTreeNodeOver.bind(this)
                    }
                },
                tbar: {
                    items: [{
                        text: t("plugin_exporttoolkit_configpanel_add"),
                        iconCls: "pimcore_icon_add",
                        handler: this.addField.bind(this, null)
                    }]
                },
                listeners: {
                    itemclick: this.onTreeNodeClick.bind(this),
                    itemcontextmenu: this.onTreeNodeContextmenu.bind(this),
                    itemmove: this.onTreeNodeMove.bind(this)
                }
            });
        }

        return this.tree;
    },

    getEditPanel: function () {
        if (!this.editPanel) {
            this.editPanel = Ext.create('Ext.tab.Panel', {
                region: "center"
            });
        }

        return this.editPanel;
    },

    onTreeNodeOver: function (targetNode, position) {
        // only appending to folders is allowed, ignore before and after
        if (position != "append" || targetNode.data.type != ExportToolkit.config.TreeType.FOLDER) return false;

        return true;
    },

    reloadTree: function () {
        this.tree.getStore().load({
            node: this.tree.getRootNode()
        });
    },

    isRootNode: function (node) {
        return node == this.tree.getRootNode();
    },

    getNodeId: function (node) {
        return (node == this.tree.getRootNode() || !node ? null : node.data.id);
    },

    onTreeNodeMove: function (node, oldParent, newParent, index) {
        var url = '/plugin/ExportToolkit/config/move';

        if (node.data.type == ExportToolkit.config.TreeType.FOLDER) {
            url = '/plugin/ExportToolkit/config/move-folder'
        }

        var to = this.getNodeId(newParent);

        Ext.Ajax.request({
            url: url,
            params: {
                who: node.data.id,
                to: to
            },
            success: function () {
                this.reloadTree();
            }.bind(this)
        });
    },

    onTreeNodeClick: function (tree, record, item, index, e, eOpts) {
        if (record.data.type == ExportToolkit.config.TreeType.FOLDER) return;

        this.openConfig(record.id);
    },

    openConfig: function (id) {
        var existingPanel = Ext.getCmp(ExportToolkit.config.ITEM_PANEL_PREFIX + id);

        if (existingPanel) {
            this.editPanel.setActiveTab(existingPanel);
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
            }.bind(this)
        });
    },

    onTreeNodeContextmenu: function (view, node, item, index, event) {
        event.stopEvent();

        view.select();

        var menu = new Ext.menu.Menu();

        if (node.data.type == ExportToolkit.config.TreeType.FOLDER) {
            menu.add(new Ext.menu.Item({
                text: t('add_folder'),
                iconCls: "pimcore_icon_folder pimcore_icon_overlay_add",
                handler: this.addFolder.bind(this, node)
            }));

            menu.add(new Ext.menu.Item({
                text: t("plugin_exporttoolkit_configpanel_add"),
                iconCls: "pimcore_icon_add",
                handler: this.addField.bind(this, node)
            }));

            if (!this.isRootNode(node)) {
                menu.add(new Ext.menu.Item({
                    text: t('delete'),
                    iconCls: "pimcore_icon_delete",
                    handler: this.deleteFolder.bind(this, node)
                }));
            }
        } else {
            menu.add(new Ext.menu.Item({
                text: t('duplicate'),
                iconCls: "pimcore_icon_clone",
                handler: this.cloneField.bind(this, node)
            }));

            menu.add(new Ext.menu.Item({
                text: t('delete'),
                iconCls: "pimcore_icon_delete",
                handler: this.deleteField.bind(this, node)
            }));
        }

        menu.showAt(event.pageX, event.pageY);
    },

    addField: function (node) {
        Ext.MessageBox.prompt(
            t('plugin_exporttoolkit_configpanel_enterkey_title'),
            t('plugin_exporttoolkit_configpanel_enterkey_prompt'),
            this.addFieldComplete.bind(this, node),
            null,
            null,
            ""
        );
    },

    isValidName: function (name) {
        var result = name.match(ExportToolkit.config.NAME_REGEX);

        return name == result && name.length > 2;
    },

    addFieldComplete: function (node, button, value) {
        var path = this.getNodeId(node);

        if (button == ExportToolkit.config.CANCEL) return;

        if (this.isValidName(value)) {
            Ext.Ajax.request({
                url: "/plugin/ExportToolkit/config/add",
                params: {
                    path: path,
                    name: value
                },
                success: function (response) {
                    var data = Ext.decode(response.responseText);

                    this.reloadTree();

                    if (!data || !data.success) {
                        pimcore.helpers.showNotification(t("error"), t("plugin_exporttoolkit_configpanel_error_adding_config"), "error", data.message);
                    } else {
                        this.openConfig(data.name);
                    }
                }.bind(this)
            });
        } else {
            Ext.Msg.alert(t("plugin_exporttoolkit_configpanel"), t("plugin_exporttoolkit_configpanel_invalid_name"));
        }
    },

    deleteField: function (node) {
        if (this.isRootNode(node)) return;

        Ext.Msg.confirm(t('delete'), t('delete_message'), function (btn) {
            if (btn == ExportToolkit.config.YES) {
                Ext.Ajax.request({
                    url: "/plugin/ExportToolkit/config/delete",
                    params: {
                        name: this.getNodeId(node)
                    }
                });

                this.getEditPanel().removeAll();
                node.remove();
            }
        }.bind(this));
    },

    addFolder: function (record) {
        Ext.MessageBox.prompt(
            t('plugin_exporttoolkit_configpanel_enterkey_title'),
            t('plugin_exporttoolkit_configpanel_enterkey_prompt'),
            this.addFolderComplete.bind(this, record),
            null,
            null,
            ""
        );
    },

    addFolderComplete: function (node, button, value) {
        if (button == ExportToolkit.config.OK && this.isValidName(value)) {
            Ext.Ajax.request({
                url: "/plugin/ExportToolkit/config/add-folder",
                params: {
                    parent: this.getNodeId(node),
                    name: value
                },
                success: function (response) {
                    var data = Ext.decode(response.responseText);

                    if (!data || !data.success) {
                        pimcore.helpers.showNotification(
                            t("error"),
                            t("plugin_exporttoolkit_configpanel_error_adding_config"),
                            "error",
                            data.message
                        );
                    } else {
                        this.reloadTree();
                    }
                }.bind(this)
            });
        }
    },

    deleteFolder: function (node) {
        if (this.isRootNode(node)) return;

        Ext.Msg.confirm(
            t('delete'),
            t('delete_message'),
            function (btn) {
                if (btn == ExportToolkit.config.YES) {
                    Ext.Ajax.request({
                        url: "/plugin/ExportToolkit/config/delete-folder",
                        params: {
                            path: this.getNodeId(node)
                        }, success: function (response) {
                            var data = Ext.decode(response.responseText);

                            if (data && data.success) {
                                this.getEditPanel().removeAll();
                                node.remove();
                            }
                        }.bind(this)
                    });
                }
            }.bind(this));
    },

    cloneField: function (node) {
        if (node.data.type != ExportToolkit.config.TreeType.CONFIGURATION) return;

        Ext.MessageBox.prompt(
            t('plugin_exporttoolkit_configpanel_enterclonekey_title'),
            t('plugin_exporttoolkit_configpanel_enterclonekey_prompt'),
            this.cloneFieldComplete.bind(this, node),
            null,
            null,
            ""
        );
    },

    cloneFieldComplete: function (node, button, value) {
        if (button == ExportToolkit.config.CANCEL) return;

        if (button == ExportToolkit.config.OK && this.isValidName(value)) {
            Ext.Ajax.request({
                url: "/plugin/ExportToolkit/config/clone",
                params: {
                    name: value,
                    originalName: this.getNodeId(node)
                },
                success: function (response) {
                    var data = Ext.decode(response.responseText);

                    this.reloadTree();

                    if (!data || !data.success) {
                        pimcore.helpers.showNotification(t("error"), t("plugin_exporttoolkit_configpanel_error_cloning_config"), "error", data.message);
                    } else {
                        this.openConfig(data.name);
                    }
                }.bind(this)
            });
        } else {
            Ext.Msg.alert(t("plugin_exporttoolkit_configpanel"), t("plugin_exporttoolkit_configpanel_invalid_name"));
        }
    }
});


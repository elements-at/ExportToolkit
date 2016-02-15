
pimcore.registerNS("pimcore.plugin.exporttoolkit.config.Item");
pimcore.plugin.exporttoolkit.config.Item = Class.create(pimcore.element.abstract, {
    initialize: function(data, parent) {
        this.parent = parent;
        this.data = data.configuration;
        this.executeScript = data.execute;

        this.tab = new Ext.TabPanel({
            activeTab: 0,
            title: this.data.general.name,
            closable: true,
            deferredRender: false,
            forceLayout: true,
            // Note, this must be the same id as used in panel.js
            id: "plugin_exporttoolkit_configpanel_panel_" + data.name,
            buttons: [{
                text: t("save"),
                iconCls: "pimcore_icon_apply",
                handler: this.save.bind(this)
            }],
            items: [this.getGeneral(), this.getAttributeClusters()]
        });


        //load attribute clusters
        if(this.data.attributeClusters && this.data.attributeClusters.length > 0) {
            for(var i=0; i < this.data.attributeClusters.length; i++) {
                this.addAttributeCluster(this.data.attributeClusters[i]);
            }
        }

        this.tab.on("activate", this.tabactivated.bind(this));
        this.tab.on("destroy", this.tabdestroy.bind(this));

        this.parent.editPanel.add(this.tab);
        this.parent.editPanel.activate(this.tab);
        this.parent.editPanel.doLayout();

        this.checkExporterStatus(false);
    },

    checkExporterStatus: function(noTimeout) {

        Ext.Ajax.request({
            url: "/plugin/ExportToolkit/config/is-export-running",
            params: {
                name: this.data.general.name
            },
            success: function(response) {
                var rdata = Ext.decode(response.responseText);
                if (rdata && rdata.success) {
                    var currentLock = rdata.locked;

                    if(this.workerLocked != currentLock) {
                        var footer = this.tab.getFooterToolbar();
                        footer.removeAll();

                        footer.add({
                            xtype: 'label',
                            itemCls: 'plugin_exporttoolkit_status',
                            text: t("plugin_exporttoolkit_configpanel_item_execute_script") + ": '" + this.executeScript + "'"
                        });


                        if(currentLock) {
                            footer.add('|');
                            footer.add({
                                xtype: 'label',
                                itemCls: 'plugin_exporttoolkit_status',
                                text: t("plugin_exporttoolkit_configpanel_item_currenty_running")
                            });
                        } else {

                            footer.add({
                                text: t("plugin_exporttoolkit_configpanel_item_start_export"),
                                iconCls: "plugin_exporttoolkit_start",
                                handler: this.startExport.bind(this)
                            });
                        }

                        footer.add({
                            text: t("save"),
                            iconCls: "pimcore_icon_apply",
                            handler: this.save.bind(this)
                        });
                        this.workerLocked = currentLock;
                    }

                } else {
                    pimcore.helpers.showNotification(t("error"), t("plugin_exporttoolkit_configpanel_item_checkerror"), "error", t(rdata.message));
                }

                if(!noTimeout && !this.tabdestroyed) {
                    window.setTimeout(function() {
                        this.checkExporterStatus();
                    }.bind(this), 10000);
                }

            }.bind(this)
        });
    },

    tabactivated: function() {
        this.setupChangeDetector();
        this.tabdestroyed = false;
    },

    tabdestroy: function() {
        this.tabdestroyed = true;
    },

    startExport: function() {
        Ext.Ajax.request({
            url: "/plugin/ExportToolkit/config/execute-export",
            params: {
                name: this.data.general.name
            },
            success: function(response) {
                var rdata = Ext.decode(response.responseText);
                if (rdata && rdata.success) {
                    this.checkExporterStatus(true);
                } else {
                    pimcore.helpers.showNotification(t("error"), t("plugin_exporttoolkit_configpanel_item_runerror"), "error", t(rdata.message));
                }
            }.bind(this)
        });
    },

    getAttributeClusters: function() {

        this.attributeClusterContainerInner = new Ext.TabPanel({
            region: "center",
            autoScroll: true,
            forceLayout: true,
            border: false,
            items: []
        });

        this.attributeClusterContainer = new Ext.Panel({
            title: t("plugin_exporttoolkit_configpanel_item_attributeClusters"),
            layout: "border",
            items: [this.attributeClusterContainerInner],
            tbar: [{
                iconCls: "pimcore_icon_add",
                handler: this.addAttributeCluster.bind(this, null),
                text: t("plugin_exporttoolkit_configpanel_item_add_attributeCluster")
            }]
        });

        this.attributeClusterContainer.on("activate", function() {
            var items = this.attributeClusterContainerInner.items.getRange();
            if(items) {
                this.attributeClusterContainerInner.activate(items[0]);
            }
        }.bind(this));

        return this.attributeClusterContainer;
    },

    getGeneral: function () {

        var classStore = pimcore.globalmanager.get("object_types_store");
        this.generalForm = new Ext.form.FormPanel({
            layout: "pimcoreform",
            bodyStyle: "padding:10px;",
            autoScroll: true,
            labelWidth: 150,
            border:false,
            title: t("plugin_exporttoolkit_configpanel_item_general"),
            items: [{
                    xtype: "textfield",
                    fieldLabel: t("text"),
                    name: "name",
                    width: 250,
                    value: this.data.general.name,
                    readOnly: true
                }, {
                    name: "description",
                    fieldLabel: t("description"),
                    xtype: "textarea",
                    width: 400,
                    height: 100,
                    value: this.data.general.description
                },
                new Ext.form.ComboBox({
                    name: "pimcoreClass",
                    //listWidth: 'auto',
                    width: 400,
                    store: classStore,
                    value: this.data.general.pimcoreClass,
                    valueField: 'id',
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_pimcoreclass"),
                    displayField: 'translatedText',
                    triggerAction: 'all',
                    listeners: {
                        //"select": this.changeClassSelect.bind(this)
                    }
                }),{
                        xtype: "textarea",
                        width: 400,
                        height: 100,
                        value: this.data ? this.data.general.sqlCondition : "",
                        name: "sqlCondition",
                        fieldLabel: t("plugin_exporttoolkit_configpanel_item_sqlCondition")
                },

                {
                    xtype: "textfield",
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_sqlOrderKey"),
                    name: "sqlOrderKey",
                    width: 400,
                    value: this.data ? this.data.general.sqlOrderKey : ""
                },
                {
                    xtype: "textfield",
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_sqlOrder"),
                    name: "sqlOrder",
                    width: 400,
                    value: this.data ? this.data.general.sqlOrder : ""
                },
                new Ext.form.ComboBox({
                    name: "queryLanguage",
                    width: 400,
                    store: pimcore.settings.websiteLanguages,
                    value: this.data.general.queryLanguage,
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_queryLanguage"),
                    triggerAction: 'all'
                }),new Ext.form.ComboBox({
                    name: "filterClass",
                    width: 400,
                    store: new Ext.data.ArrayStore({
                        autoDestroy: true,
                        idIndex: 0,
                        url: "/plugin/ExportToolkit/config/get-classes?type=export-filter",
                        fields: [
                            'classname'
                        ]
                    }),
                    value: this.data.general.filterClass,
                    valueField: 'classname',
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_filterclass"),
                    displayField: 'classname',
                    triggerAction: 'all'
                }),
                new Ext.form.ComboBox({
                    name: "conditionModificator",
                    width: 400,
                    store: new Ext.data.ArrayStore({
                        autoDestroy: true,
                        idIndex: 0,
                        url: "/plugin/ExportToolkit/config/get-classes?type=export-conditionmodificator",
                        fields: [
                            'classname'
                        ]
                    }),
                    value: this.data.general.conditionModificator,
                    valueField: 'classname',
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_conditionmodificator"),
                    displayField: 'classname',
                    triggerAction: 'all'
                }),
                {

                    xtype: 'checkbox',
                    fieldLabel: t('plugin_exporttoolkit_configpanel_item_useSaveHook'),
                    name: 'useSaveHook',
                    checked: this.data.general.useSaveHook
                },{

                    xtype: 'checkbox',
                    fieldLabel: t('plugin_exporttoolkit_configpanel_item_useDeleteHook'),
                    name: 'useDeleteHook',
                    checked: this.data.general.useDeleteHook
                },
                {
                    xtype: "textfield",
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_executor"),
                    name: "executor",
                    width: 400,
                    value: this.data ? this.data.general.executor : ""
                },
                {
                    name: "additionalData",
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_additional_data"),
                    xtype: "textarea",
                    width: 400,
                    height: 100,
                    value: this.data.general.additionalData
                }
            ]
        });

        return this.generalForm;
    },

    addAttributeCluster: function (data) {
        var myId = Ext.id();

        var item =  new Ext.form.FormPanel({
            layout: "pimcoreform",
            id: myId,
            autoScroll: true,
            forceLayout: true,
            labelWidth: 150,
            title: (data && data.attributeClusterName) ? t("plugin_exporttoolkit_configpanel_item_attributeCluster") + ": " + data.attributeClusterName : t("plugin_exporttoolkit_configpanel_item_attributeCluster"),
            bodyStyle: "padding: 10px 10px 10px 10px; min-height:40px;",
            tbar: this.getTopBar(myId, this.attributeClusterContainerInner),

            items: [{
                    xtype: "textfield",
                    width: 500,
                    value: data ? data.attributeClusterName : "",
                    name: "attributeClusterName",
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_attributeClusterName")
                },new Ext.form.ComboBox({
                    name: "clusterInterpreterClass",
                    listWidth: 'auto',
                    width: 500,
                    store: new Ext.data.ArrayStore({
                        // store configs
                        autoDestroy: true,
                        // reader configs
                        idIndex: 0,
                        url: "/plugin/ExportToolkit/config/get-classes?type=attribute-cluster-interpreter",
                        fields: [
                            'classname'
                        ]
                    }),
                    value: data ? data.clusterInterpreterClass : null,
                    valueField: 'classname',
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_clusterInterpreterClass"),
                    displayField: 'classname',
                    triggerAction: 'all'
                }),{
                    xtype: "textarea",
                    width: 500,
                    height: 100,
                    value: data ? JSON.stringify(data.attributeClusterConfig, null, " ") : "",
                    name: "attributeClusterConfig",
                    enableKeyEvents: true,
                    validator: function(value) {
                        try {
                            Ext.decode(value);
                            return true;
                        } catch(e) {
                            return false;
                        }
                    },
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_attributeClusterConfig")
                }, this.getAttributeTable(data)
            ]
        });


        this.attributeClusterContainerInner.add(item);
        item.doLayout();
        this.attributeClusterContainerInner.doLayout();
        if(!data) {
            this.attributeClusterContainerInner.activate(item);
        }
    },

    getAttributeTable: function(data) {

        var store = new Ext.data.JsonStore({
            data: (data && data.attributes) ? data.attributes : [],
            fields: ["name", "fieldname", "locale", "attributeGetterClass", "attributeInterpreterClass", "attributeConfig"]
        });

        var columns = [];
        columns.push({header: t('name'), dataIndex: 'name', width: 200, editor: new Ext.form.TextField({})});
        columns.push({header: t('plugin_exporttoolkit_configpanel_item_fieldname'), dataIndex: 'fieldname', width: 200, editor: new Ext.form.TextField({})});
        columns.push({header: t('plugin_exporttoolkit_configpanel_item_locale'), dataIndex: 'locale', width: 100, editor: new Ext.form.TextField({})});

        columns.push({
            xtype: 'actioncolumn',
            width: 30,
            items: [
                {
                    tooltip: t('plugin_exporttoolkit_configpanel_item_attributeAdvanced'),
                    icon: "/pimcore/static/img/icon/cog_edit.png",
                    handler: function (grid, rowIndex) {
                        grid.getSelectionModel().selectRow(rowIndex);
                        var data = grid.getStore().getAt(rowIndex);
                        var dialog = new pimcore.plugin.exporttoolkit.config.AttributeConfig(this.updateData, data, grid);
                        dialog.show();
                    }.bind(this)
                }
            ]
        });

        columns.push({
            xtype: 'actioncolumn',
            width: 30,
            items: [
                {
                    tooltip: t('up'),
                    icon: "/pimcore/static/img/icon/arrow_up.png",
                    handler: function (grid, rowIndex) {
                        if(rowIndex > 0) {
                            var rec = grid.getStore().getAt(rowIndex);
                            grid.getStore().removeAt(rowIndex);
                            grid.getStore().insert(rowIndex-1, [rec]);
                        }
                    }.bind(this)
                }
            ]
        });
        columns.push({
            xtype: 'actioncolumn',
            width: 30,
            items: [
                {
                    tooltip: t('down'),
                    icon: "/pimcore/static/img/icon/arrow_down.png",
                    handler: function (grid, rowIndex) {
                        if(rowIndex < (grid.getStore().getCount()-1)) {
                            var rec = grid.getStore().getAt(rowIndex);
                            grid.getStore().removeAt(rowIndex);
                            grid.getStore().insert(rowIndex+1, [rec]);
                        }
                    }.bind(this)
                }
            ]
        });

        columns.push({
            xtype: 'actioncolumn',
            width: 30,
            items: [
                {
                    tooltip: t('remove'),
                    icon: "/pimcore/static/img/icon/cross.png",
                    handler: function (grid, rowIndex) {
                        grid.getStore().removeAt(rowIndex);
                    }.bind(this)
                }
            ]
        });

        var attributeTable = new Ext.grid.EditorGridPanel({
            id: Ext.id(),
            title: t("plugin_exporttoolkit_configpanel_item_attributes"),
            tbar: [{
                id: Ext.id(),
                iconCls: "pimcore_icon_add",
                handler: function(store) {
                    store.add(new Ext.data.Record({name: ""}));
                }.bind(this, store),
                text: t("plugin_exporttoolkit_configpanel_item_add_attribute")
            }],
            store: store,
            trackMouseOver: true,
            sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
            columnLines: true,
            stripeRows: true,
            colModel: new Ext.grid.ColumnModel({
                defaults: {
                    sortable: false
                },
                columns: columns
            }),
            autoHeight: true
        });

        return attributeTable;

    },

    updateData: function(data, grid) {
        //thing to do there
    },


    getTopBar: function (index, container) {
        return [
            {
                iconCls: "pimcore_icon_up",
                handler: function (blockId, container) {

                    //var container = parent.attributeClusterContainerInner;
                    var blockElement = Ext.getCmp(blockId);
                    var index = pimcore.settings.targeting.conditions.detectBlockIndex(blockElement, container);
                    var tmpContainer = pimcore.viewport;

                    var newIndex = index-1;
                    if(newIndex < 0) {
                        newIndex = 0;
                    }

                    // move this node temorary to an other so ext recognizes a change
                    container.remove(blockElement, false);
                    tmpContainer.add(blockElement);
                    container.doLayout();
                    tmpContainer.doLayout();

                    // move the element to the right position
                    tmpContainer.remove(blockElement,false);
                    container.insert(newIndex, blockElement);
                    container.doLayout();
                    tmpContainer.doLayout();

                    pimcore.layout.refresh();
                }.bind(window, index, container)
            },{
                iconCls: "pimcore_icon_down",
                handler: function (blockId, container) {

                    //var container = parent.attributeClusterContainerInner;
                    var blockElement = Ext.getCmp(blockId);
                    var index = pimcore.settings.targeting.conditions.detectBlockIndex(blockElement, container);
                    var tmpContainer = pimcore.viewport;

                    // move this node temorary to an other so ext recognizes a change
                    container.remove(blockElement, false);
                    tmpContainer.add(blockElement);
                    container.doLayout();
                    tmpContainer.doLayout();

                    // move the element to the right position
                    tmpContainer.remove(blockElement,false);
                    container.insert(index+1, blockElement);
                    container.doLayout();
                    tmpContainer.doLayout();

                    pimcore.layout.refresh();
                }.bind(window, index, container)
            },"->",{
                iconCls: "pimcore_icon_delete",
                handler: function (index, container) {
                    container.remove(Ext.getCmp(index));
                    //parent.attributeClusterContainerInner.remove(Ext.getCmp(index));
                }.bind(window, index, container)
            }];
    },

    getSaveData: function() {

        var saveData = {};
        saveData["general"] = this.generalForm.getForm().getFieldValues();

        var attributeClusterDataArray = [];

        var attributeClusters = this.attributeClusterContainerInner.items.getRange();
        for (var i = 0; i < attributeClusters.length; i++) {
            var attributeClusterData = attributeClusters[i].getForm().getFieldValues();
            try {
                attributeClusterData.attributeClusterConfig = Ext.decode(attributeClusterData.attributeClusterConfig);
            } catch(e) {
                console.log(e);
            }

            var items = attributeClusters[i].items.getRange();
            var grid = items[items.length-1];
            var store = grid.getStore();

            var attributesData = [];
            store.each(function(rec) {
                attributesData.push(rec.data);
            });

            attributeClusterData.attributes = attributesData;
            attributeClusterDataArray.push(attributeClusterData);
       }
        saveData["attributeClusters"] = attributeClusterDataArray;

        return Ext.encode(saveData);
    },

    save: function () {
        var saveData = this.getSaveData();

        Ext.Ajax.request({
            url: "/plugin/ExportToolkit/config/save",
            params: {
                data: saveData
            },
            method: "post",
            success: function (response) {
                var rdata = Ext.decode(response.responseText);
                if (rdata && rdata.success) {
                    pimcore.helpers.showNotification(t("success"), t("plugin_exporttoolkit_configpanel_item_save_success"), "success");
                    this.resetChanges();

                    var attributeClusters = this.attributeClusterContainerInner.items.getRange();
                    for (var i = 0; i < attributeClusters.length; i++) {
                        var items = attributeClusters[i].items.getRange();
                        var grid = items[items.length-1];
                        var store = grid.getStore();
                        store.commitChanges();
                    }

                }
                else {
                    pimcore.helpers.showNotification(t("error"), t("plugin_exporttoolkit_configpanel_item_saveerror"), "error", t(rdata.message));
                }
            }.bind(this)
        });
    }

});

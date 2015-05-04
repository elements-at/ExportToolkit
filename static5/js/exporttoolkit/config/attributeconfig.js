pimcore.registerNS("pimcore.plugin.exporttoolkit.config.AttributeConfig");
pimcore.plugin.exporttoolkit.config.AttributeConfig = Class.create({

    initialize: function (callback, data, grid) {
        this.callback = callback;
        this.data = data;
        this.grid = grid;
    },

    show: function() {
        this.window = new Ext.Window({
            layout:'fit',
            width:700,
            height:250,
            autoScroll: true,
            title: t("plugin_exporttoolkit_configpanel_item_attributeAdvanced"),
            closeAction:'close',
            modal: true
        });

        var initialConfig = this.data && this.data.data.attributeConfig ?  JSON.stringify(this.data.data.attributeConfig, null, " ") : '""';

        this.form = new Ext.form.FormPanel({
            bodyStyle: "padding: 10px;",
            border: false,
            defaults: {
                width: 600
            },
            items: [
                new Ext.form.ComboBox({
                    name: "attributeGetterClass",
                    listWidth: 'auto',
                    store: new Ext.data.ArrayStore({
                        autoDestroy: true,
                        idIndex: 0,
                        proxy: {
                            type: 'ajax',
                            url: "/plugin/ExportToolkit/config/get-classes?type=attribute-getter"
                        },
                        fields: [
                            'classname'
                        ]
                    }),
                    value: this.data ? this.data.data.attributeGetterClass : null,
                    valueField: 'classname',
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_attributeGetterClass"),
                    displayField: 'classname',
                    triggerAction: 'all'
                }),new Ext.form.ComboBox({
                    name: "attributeInterpreterClass",
                    listWidth: 'auto',
                    store: new Ext.data.ArrayStore({
                        autoDestroy: true,
                        idIndex: 0,
                        proxy: {
                            type: 'ajax',
                            url: "/plugin/ExportToolkit/config/get-classes?type=attribute-interpreter"
                        },
                        fields: [
                            'classname'
                        ]
                    }),
                    value: this.data ? this.data.data.attributeInterpreterClass : null,
                    valueField: 'classname',
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_attributeInterpreterClass"),
                    displayField: 'classname',
                    triggerAction: 'all'
                }),{
                    xtype: "textarea",
                    name: "attributeConfig",
                    height: 100,
                    value: initialConfig,
                    validator: function(value) {
                        try {
                            Ext.decode(value);
                            return true;
                        } catch(e) {
                            return false;
                        }
                    },
                    fieldLabel: t("plugin_exporttoolkit_configpanel_item_attributeConfig")
                }
            ]
        });

        this.window.add(new Ext.Panel({
            items: [this.form],
            buttons: [
                {
                    xtype: "button",
                    iconCls: "pimcore_icon_apply",
                    text: t('apply'),
                    handler: this.applyData.bind(this)
                }
            ]
        }));
        this.window.show();
    },

    applyData: function() {
        var value = this.form.getForm().getFieldValues();
        this.window.close();

        value.attributeConfig = Ext.decode(value.attributeConfig);
        for (var property in value) {
            this.data.data[property] = value[property];
        }
        this.callback(this.data, this.grid);
    }
});
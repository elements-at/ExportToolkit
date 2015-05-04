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

        this.form = new Ext.form.FormPanel({
            layout: "pimcoreform",
            bodyStyle: "padding: 10px;",
            border: false,
            items: [
                new Ext.form.ComboBox({
                    name: "attributeGetterClass",
                    listWidth: 'auto',
                    width: 500,
                    store: new Ext.data.ArrayStore({
                        autoDestroy: true,
                        idIndex: 0,
                        url: "/plugin/ExportToolkit/config/get-classes?type=attribute-getter",
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
                    width: 500,
                    store: new Ext.data.ArrayStore({
                        autoDestroy: true,
                        idIndex: 0,
                        url: "/plugin/ExportToolkit/config/get-classes?type=attribute-interpreter",
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
                    width: 500,
                    height: 100,
                    value: this.data ?  JSON.stringify(this.data.data.attributeConfig, null, " ") : null,
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
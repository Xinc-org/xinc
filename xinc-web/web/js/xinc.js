/*
Ext.Loader.setConfig({enabled: true});
Ext.Loader.setPath('Ext.ux', '../ux');
*/
/*Ext.Loader.setConfig({enabled:true});
Ext.require([
    'Ext.grid.*',
    'Ext.data.*',
    'Ext.util.*',
    'Ext.Action',
    'Ext.tab.*',
    'Ext.button.*',
    'Ext.form.*',
    'Ext.layout.container.*',
    'Ext.resizer.Splitter',
    'Ext.fx.target.Element',
    'Ext.fx.target.Component',
    'Ext.window.Window',
    'Ext.app.Portlet',
    'Ext.app.PortalColumn',
    'Ext.app.PortalPanel',
    'Ext.app.Portlet',
    'Ext.app.PortalDropZone',
    'Ext.app.GridPortlet',
    'Ext.app.ChartPortlet',
    'Ext.layout.container.Card',
    'Ext.layout.container.Border',
]);
*/

Ext.onReady(function(){
    appXinc = new Ext.app.Xinc();
    Ext.get('loading').fadeOut({remove:true});
});

var store = Ext.create('Ext.data.TreeStore', {
    sorters: [{
        property: 'leaf',
        direction: 'ASC'
    },{
        property: 'text',
        direction: 'ASC'
    }],
    fields: [{
        name: 'id',
        type: 'string'
    }, {
        name: 'name',
        type: 'string'
    }, {
        name: 'url',
        type: 'string'
    }, {
        name: 'text',
        type: 'string'
    }, {
        name: 'leaf',
        type: 'string'
    }],
    root: MenuItems
});

Ext.define('Ext.app.Xinc', {
    extend: 'Ext.container.Viewport',

    initComponent: function(){
        Ext.apply(this, {
            id: 'app-viewport',
            layout: {
                type: 'border',
                padding: '0 2 2 2' // pad the layout from the window edges
            },
            items: [{
                id: 'header',
                xtype: 'box',
                region: 'north',
                height: 40,
                html: Ext.get('header-content').dom.innerHTML
            },{
                xtype: 'container',
                region: 'center',
                layout: 'border',
                items: [{
                    id: 'options',
                    title: 'Options',
                    region: 'west',
                    animCollapse: true,
                    width: 200,
                    minWidth: 150,
                    maxWidth: 400,
                    split: true,
                    collapsible: true,
                    layout: {
                        type: 'accordion',
                        animate: true
                    },
                    items: [{
                        title:'Projects',
                        autoScroll: true,
                        border: false,
                        layout: 'fit',
                        items: new Ext.create('Ext.tree.Panel', {
                            store: store,
                            hideHeaders: true,
                            rootVisible: true,
                            collapsible: false,
                            autoScroll: true,
                            resizeable: true,
                            forceFit: true,
                            listeners: {
                                beforeselect: function(treemodel, model) {
                                    if (model.data.url != null) {
                                        appXinc.addNewTab(model.data.id, model.data.name, model.data.url);
                                        return false;
                                    }
                                    return model.isLeaf();
                                }
                            }
                        })
                    },{
                        title:'Settings',
                        autoScroll: true,
                        border: false,
                    }]
                }, this.createTabContainer()
                ]
            }]
        });
        this.callParent(arguments);
    },

    createTabContainer: function() {
        this.tabContainer = Ext.createWidget('tabpanel', {
            id: 'options2',
            xtype: 'tabpanel',
            region: 'center',
            activeGroup: 0,
            items: [{
                id:'widget-dashboard',
                title: 'Dashboard',
                loader: {
                    autoLoad: true,
                    url: './dashboard/projects',
                    scope: this,
                    nocache:true,
                    loadMask: true,
                    timeout:30000
                },
                iconCls:'icon-dashboard',
                autoScroll: true,
                closable: false,
            }]
        });

        return this.tabContainer;
    },

    addTabBuild: function(name, timestamp, label) {
        this.addNewTab(
            'project-'+ name + '-' + timestamp,
            label + ' - ' + name,
            './dashboard/detail?project=' + name + '&timestamp=' + timestamp
        );
    },

    addNewTab: function(id, name, url, showHtml = true) {
        var tab=Ext.getCmp(id);
        if(tab) {
            tab.show();
            return tab;
        }

        this.tabContainer.add({
            id: id,
            autoScroll: true,
            title: name,
            autoDestroy: true,
            closable: true,
            loader: {
                autoLoad: true,
                url: url,
                scope: this,
                scripts: true,
                nocache: true,
                loadMask: true,
                timeout: 30000,
                renderer: (showHtml ? 'html' : function(loader, response, active)  {
                    loader.getTarget().update('<pre>' + text + '</pre>');
                    return true;
                })
            },
        }).show();
        return tab;
    }
});

Ext.define("BS.BlueSpiceInsertTemplate.dialog.InsertTemplate", {
    extend: "BS.Window",
    bodyStyle: {
        backgroundColor: "#fff"
    },
    requires:[
        'Ext.Button'
    ],
    modal: true,
    width: 600,
    height: 500,
    layout: 'border',
    title: mw.message('bs-inserttemplate-dialog-title').plain(),
    afterInitComponent: function() {
        this.templateStore = Ext.create( 'BS.store.BSApi', {
            apiAction: 'bs-inserttemplate-data-store',
            fields: ['id', 'name', 'desc', 'code' ],
            submitValue: false,
            remoteSort: false,
            remoteFilter: false,
            proxy: {
                type: 'ajax',
                url: mw.util.wikiScript('api'),
                extraParams: {
                    format: 'json',
                    limit: 0
                },
                reader: {
                    type: 'json',
                    root: 'results',
                    idProperty: 'name'//,
                }
            },
            sortInfo: {
                field: 'name'
            }
        });

        this.templateStore.on( 'load', this.onStoreLoad, this );

        this.templateGrid = Ext.create( 'Ext.form.ComboBox', {
            triggerAction: 'all',
            editable: true,
            minChars: 1,
            allowBlank: false,
            forceSelection: true,
            value: 'id',
            store: this.templateStore,
            displayField: 'name',
            valueField: 'id'
        });

        this.templateGrid.on( 'select', this.onRowSelect, this );

        this.syntaxTextArea = Ext.create( 'Ext.form.TextArea', {
            hideLabel: true,
            name: 'syntaxTextArea',
            flex: 1
        });

        this.syntaxTextArea.on( 'blur', this.onSyntaxTextAreaBlur, this );

        this.syntaxPanel = Ext.create('Ext.Panel', {
            title: mw.message('bs-inserttemplate-label-second').plain(),
            border: true,
            flex: 1,
            layout: 'fit',
            items: [ this.syntaxTextArea ]
        });

        this.previewPanel = Ext.create('Ext.Panel', {
            title: mw.message('bs-inserttemplate-label-desc').plain(),
            tools: [{
                type: 'refresh'
            }],
            border: true,
            flex: 1,
            autoScroll: true
        });

        this.pnlNorth = Ext.create('Ext.Container', {
            region: 'north',
            padding: 5,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                Ext.create( 'Ext.form.Label', { text: mw.message('bs-inserttemplate-label-first').plain() }),
                this.templateGrid
            ]
        });

        this.pnlWest = Ext.create('Ext.Container', {
            region: 'west',
            width: 250,
            padding: 5,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                // this.templateGrid,
                this.syntaxPanel
            ]
        });

        this.pnlCenter = Ext.create('Ext.Container', {
            region: 'center',
            border: false,
            padding: 5,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items:[
                this.previewPanel
            ]
        });

        this.items = [
            this.pnlNorth,
            this.pnlWest,
            this.pnlCenter
        ];

        this.callParent(arguments);
    },

    /**
     *
     * @param {Ext.form.field.TextArea} sender
     * @param e
     * @param eOpts
     */
    onSyntaxTextAreaBlur: function ( sender, e, eOpts ) {
        var api = new mw.Api();
        var me = this;
        api.get({
            'action': 'parse',
            'text': sender.getValue()
        })
        .done( function( response, jqHXR ) {
            me.previewPanel.update( response.parse.text['*'] );
        });
    },

    getData: function() {
        this.currentData.code = this.syntaxTextArea.getValue();
        return this.currentData;
    },

    setData: function( obj ) {
        this.syntaxTextArea.setValue( obj.code );
        this.syntaxTextArea.fireEvent('blur', this.syntaxTextArea);
        this.callParent( arguments );
    },

    onStoreLoad: function( store, records, options ) {
        this.templateStore.sort( 'name', 'ASC' );
    },

    // onRowSelect: function( grid, records, index, eOpts ) {
    onRowSelect: function( combo, records, eOpts ) {
        var data = {
            desc : records[0].get( 'desc' ),
            // type : records[0].get( 'type' )
        };
        // this.currentData.type = data.type;
        this.currentData.name = records[0].get( 'name' );

        this.setCommonFields( records[0].get( 'code' ), data );
    },

    setCommonFields: function( text, data ) {
        var desc = data.desc;
        if ( typeof( data.examples ) !== "undefined" && data.examples != '' ) {
            desc = desc
                + '<br/><br/><strong>'
                + mw.message( 'bs-inserttemplate-label-examples' ).plain()
                + '</strong>';
            for ( var i = 0; i < data.examples.length; i++ ) {
                desc = desc + '<br/><br/>';
                var example = data.examples[i];
                if ( typeof( example.label ) !== "undefined" && example.label != '' ) {
                    desc = desc
                        + $( '<div>', { text: example.label } ).wrap( '<div/>' ).parent().html();
                };
                if ( typeof( example.code ) !== "undefined" && example.code != '' ) {
                    desc = desc
                        + $( '<code>', { style: 'white-space:pre-wrap;', text: example.code } ).wrap( '<div/>' ).parent().html();
                }
            }
        }
        if ( typeof( data.helplink ) !== "undefined" && data.helplink != '' ) {
            desc = desc
                + '<br/><br/><strong>'
                + mw.message( 'bs-inserttemplate-label-see-also' ).plain()
                + '</strong><br/><br/>'
                + $( '<a>', { href: data.helplink, target: '_blank', text: data.helplink } ).wrap( '<div/>' ).parent().html();
        }
        this.previewPanel.update( desc );
        this.syntaxTextArea.setValue( text );
        this.syntaxTextArea.focus();

        var start = text.indexOf('"') + 1;
        var end = text.indexOf('"', start );
        // if( data.type != 'tag' ) {
        //     start = start - 1;
        //     end = end + 1;
        // }

        this.syntaxTextArea.selectText(start, end);
    }
});


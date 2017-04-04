$(document).on( 'click', '#bs-editButton-insertTemplate', function( e ){
    var me = this;

    var insertTemplateDialog = Ext.create( 'BS.BlueSpiceInsertTemplate.dialog.InsertTemplate' );

    insertTemplateDialog.on( 'ok', BsInsertTemplateWikiTextConnector.applyData );

    insertTemplateDialog.setData(
        BsInsertTemplateWikiTextConnector.getData()
    );

    insertTemplateDialog.show();

    e.preventDefault();
    return false;
});

$(document).bind('BsVisualEditorActionsInit', function( event, plugin, buttons, commands, menus ){
	menus.push({
		menuId: 'bsContextTemplate',
		menuConfig: {
			text: mw.message('bs-insertTemplate-button-template-title').plain(),
			icon: 'template',
			cmd : 'mceBsTemplate'
		}
	});
	buttons.push({
		buttonId: 'bstemplate',
		buttonConfig: {
			title : mw.message('bs-insertTemplate-button-template-title').plain(),
			cmd : 'mceBsTemplate',
			icon: 'template',
			onPostRender: function() {
				var self = this;

				tinyMCE.activeEditor.on('NodeChange', function(evt) {
					self.disabled(false);
					$(evt.parents).each(function(){
						if ( this.tagName.toLowerCase() == 'pre' ) {
							self.disabled(true);
						}
					});
				});
			}
		}
	});

	commands.push({
		commandId: 'mceBsTemplate',
		commandCallback: function() {

            BsInsertTemplateVisualEditorConnector.caller = this;

            var insertTemplateDialog = Ext.create( 'BS.BlueSpiceInsertTemplate.dialog.InsertTemplate' );

            insertTemplateDialog.on( 'ok', BsInsertTemplateVisualEditorConnector.applyData );

            insertTemplateDialog.setData(
                BsInsertTemplateVisualEditorConnector.getData()
            );

            insertTemplateDialog.show();

            e.preventDefault();
            return false;
		}
	});
});

var BsInsertTemplateWikiTextConnector = {
    getData: function() {
        bs.util.selection.reset();
        var currentCode = bs.util.selection.save();

        var data = {
            code: currentCode
        };
        return data;
    },

    applyData: function( sender, data ) {
        bs.util.selection.restore( data.code );
    }
};

var BsInsertTemplateVisualEditorConnector = {
    data: {},
    getData: function() {
        var me = BsInsertTemplateVisualEditorConnector;
        var node = me.caller.selection.getNode();
        me.data.isInsert = false;
        me.data.id = node.getAttribute('data-bs-id');
        me.data.type = node.getAttribute('data-bs-type');
        me.data.name = node.getAttribute('data-bs-name');

        var templates = me.caller.plugins.bswikicode.getTemplateList();
        var currentCode = templates[me.data.id];

        me.data.code = currentCode;
        return me.data;
    },

    applyData: function(  sender, data ) {
        var me = BsInsertTemplateVisualEditorConnector;
        me.bookmark = me.caller.selection.getBookmark();
        me.caller.selection.moveToBookmark( me.bookmark );
        var selectedNode = me.caller.selection.getNode();
        var code = data.code;

        var specialtags = me.caller.plugins.bswikicode.getTemplateList();
		if( me.data.id ) {
			specialtags[me.data.id] = code;
		} else {
			me.data.id = specialtags.length;
			specialtags.push(code);
		}

		var spanAttrs = {
			'id': 'bs_template:@@@TPL'+me.data.id+'@@@',
			'class':'template',
			'data-bs-name': me.data.name,
			'data-bs-type': 'template',
			'data-bs-id': me.data.id
		};
		var spanContent = '{{ '+me.data.name+' }}';
		spanAttrs['class'] += ' mceNonEditable';

        var newSpanNode = null;
        if ( selectedNode.nodeName.toLowerCase() == 'span') {
            newSpanNode = me.caller.dom.create( 'span', spanAttrs, spanContent );
            me.caller.dom.replace(newSpanNode, selectedNode);
            //Place cursor to end
            me.caller.selection.select(newSpanNode, false);
        } else {
            newSpanNode = me.caller.dom.createHTML( 'span', spanAttrs, spanContent );
            me.caller.insertContent(newSpanNode);
        }

        me.caller.selection.collapse(false);
    }
};
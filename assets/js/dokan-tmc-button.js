jQuery(document).ready(function($) {

    tinymce.create('tinymce.plugins.dokan_button', {
        init : function(editor, url) {
                var menuItem = [];
                
                $.each( dokan_shortcodes, function( i, val ){
                    var tempObj = {
                            text : val.title,
                            onclick: function() {
                                editor.insertContent(val.content);
                            }
                        };
                        
                    menuItem.push( tempObj );
                } );
                
                // Register buttons - trigger above command when clickeditor
                editor.addButton('dokan_button', {
                    title : 'Dokan shortcodes', 
                    type  : 'menubutton',
                    text  : 'Dokan',
//                    image : 'https://cdn3.iconfinder.com/data/icons/softwaredemo/PNG/32x32/Circle_Green.png',
                    menu  : menuItem
                });
        },   
    });

    // Register our TinyMCE plugin
    
    tinymce.PluginManager.add('dokan_button', tinymce.plugins.dokan_button);
});
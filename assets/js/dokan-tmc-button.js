jQuery(document).ready(function($) {

    tinymce.create('tinymce.plugins.dokan_button', {
        init : function(editor, url) {

                // Register buttons - trigger above command when clickeditor
                editor.addButton('dokan_button', {
                    title : 'Dokan shortcodes', 
                    type  : 'menubutton',
                    text  : 'Dokan',
//                    image : 'https://cdn3.iconfinder.com/data/icons/softwaredemo/PNG/32x32/Circle_Green.png',
                    menu  : [
                                {
                                    text: 'Menu item 1',
                                    onclick: function() {
                                      editor.insertContent('&nbsp;<strong>Menu item 1 here!</strong>&nbsp;');
                                    }
                                },
                                {
                                    text: 'Menu item 2',
                                    onclick: function() {
                                      editor.insertContent('&nbsp;<em>Menu item 2 here!</em>&nbsp;');
                                    }
                                }
                            ]
                });
        },   
    });

    // Register our TinyMCE plugin
    
    tinymce.PluginManager.add('dokan_button', tinymce.plugins.dokan_button);
});
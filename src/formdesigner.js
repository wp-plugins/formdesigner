(function() {
    tinymce.create('tinymce.plugins.FormDesigner', {
        init : function(ed, url) {
        	ed.addCommand('formdesignerPopup', function() {
                ed.windowManager.open({
                    file   : ajaxurl+'?action=formdesigner_popup',
                    width  : 600, 
                    height : 160
                });
            });
            ed.addButton('FormDesigner', {
                title : 'Конструктор форм',
                image : url+'/logo.png',
                cmd : 'formdesignerPopup'
            });
        },
        getInfo : function() {
            return {
                longname : "Конструктор форм FormDesigner.ru",
                author : 'FormDesigner.ru',
                authorurl : 'http://formdesigner.ru',
                infourl : 'http://formdesigner.ru',
                version : "1.0"
            };
        }
    });
    tinymce.PluginManager.add('FormDesigner', tinymce.plugins.FormDesigner);
})();
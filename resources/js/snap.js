jQuery(function($){
    // upload buttons
    $('.snap-upload-button').click(function(){
        var send_to_editor = window.send_to_editor,
            tb_remove = window.tb_remove,
            self = this
            ;
            
        window.send_to_editor = function(html){
            var src = $('img', html).attr('src');
            $(self).prev().val( src );
            tb_remove();
            return false;
        }
        
        window.tb_remove = function(){
            window.send_to_editor = send_to_editor;
            window.tb_remove = tb_remove;
            tb_remove();
        }
        
        tb_show('', 'media-upload.php?type=image&TB_iframe=true');
        
    });
});
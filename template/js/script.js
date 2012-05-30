jQuery(function($){
    
    
    var label = $('input[name=agree]').parents('label').find('span');
    html = label.html();
    label.html(html.replace(/(official.*?rules)/i, '<a class="rules-link" href="#rules">$1</a>'));
    label.find('a.rules-link').click(function(e){
        e.preventDefault();
        $('.nav-tabs .rules a').tab('show');
        window.scrollTo(0,0);
    });
    
    
    var onResize = function(){
        var w = $(window).width();
        if( w < 500 ){
            $('form').removeClass('form-horizontal');
        }
        else{
            $('form').addClass('form-horizontal');
        }
    };
    
    $(window).resize( onResize );
    onResize();
});
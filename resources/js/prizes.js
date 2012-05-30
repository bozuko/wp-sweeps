jQuery(function($){
    var input = $('input[name=prizes]')
      , data = []
      , ids = 0
      , form = $('.prize-form').clone()
      , formTpl = $('.new-form').find('.prize-form')
      , container = $('.prizes-container')
      , metaBox = container.parents('.postbox')
      , addBtn = container.find('.add-prize')
      ;
    
    try {
        data = JSON.parse( input.val() );
    }
    catch(e){
        data = [];
    }
    
    container.parents('form').submit(function(){ update_value(); });
    
    container.insertAfter( metaBox );
    metaBox.remove();
    
    container.sortable({
        revert: true,
        update: update_value
    });
    
    addBtn.click(function(){ add_prize_form(); } );
    
    if( data.length ) for(var i = 0; i < data.length; i++){
        add_prize_form( data[i] );
    }
    
    else {
        add_prize_form();
    }
    
    function add_prize_form( prize )
    {
        var form = formTpl.clone();
        form.find('script').remove();
        form.addClass('real-form');
        
        // create unique ids
        form.find('label').each(function(){
            var id = $(this).attr('for');
            new_id = id+'_'+(++ids);
            $(this).attr('for', new_id);
            form.find('#'+id)[0].id = new_id;
        });
        
        if( prize ) form.find('input,textarea').each(function(){
            var n = $(this).attr('name');
            if( prize[n] ){
                $(this).val( prize[n] );
                if(n=='image' && prize['image_url']){
                    form.find('.snap-upload-button').snapupload('update_image', prize.image_url );
                }
            }
        });
        
        // add our listeners
        form.find('input,textarea').change( update_value );
        
        form.find('.remove-prize').click(function(){
            if( confirm('Are you sure you would like to remove this prize?') ){
                form.remove();
                update_value();
            }
        });
        
        form.insertBefore(addBtn);
        update_numbers();
    }
    
    function update_value()
    {
        var prizes = [];
        container.find('.real-form').each(function(){
            var prize = {};
            $(this).find('input,textarea').each(function(){
                prize[$(this).attr('name')] = $(this).val();
            });
            prizes[prizes.length] = prize;
        });
        input.val( JSON.stringify( prizes ) );
        
        update_numbers();
    }
    
    function update_numbers()
    {
        container.find('.real-form').each(function(num, form){
            $(this).find('.header').html('Prize '+(num+1));
        });
    }
    
    /*
    addForm.find('input,textarea').keypress(function(e){
        if( e.keyCode == 13 )
        {
            e.preventDefault();
            add_prize();
        }
    });
    */
    
    // lets add the 
    // initialize the data
    
});
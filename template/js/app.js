var Sweeps ={Campaign:{}};

jQuery(function($){
    
    var _loadtime = new Date();
    
    if( window.parent == window.top && Sweep_Campaign.is_facebook_tab ){
        // window.location = window.location.href.replace(/facebook_tab/i, 'not_facebook_tab');
    }
    
    if( window.FB ) FB.init({
        appId       :Sweep_Campaign.facebook_id, // App ID
        channelUrl  :Sweep_Campaign.channel_url, // Channel File
        oauth       :true,
        status      :true, // check login status
        cookie      :true, // enable cookies to allow the server to access the session
        xfbml       :true  // parse XFBML
    });
    
    (function form(){
        var $form = $('.the-form form');
        
        if( !$form.length || window.custom_form_handler ) return;
        
        var $form_ct = $form.parents('.indented-form')
          , action = $form.attr('action')
          , method = $form.attr('method').toLowerCase()
          , $submit = $form.find('input[type=submit]')
          , $submitText = $submit.val()
          , $tab = $('#enter-tab')
          ;
        
        $form.append($('<input type="hidden" name="_ajax_" value="1" />'));
        
        $form.unbind('submit');
        $form.submit(function(e){
            e.preventDefault();
            $submit.val('Entering...').attr('disabled', true);
            $form_ct.addClass('submitting');
            
            $.ajax({
                url             :action,
                type            :method,
                data            :$form.serialize()
            }).done( function(data){
                
                $form.find('.messages').html('');
                $form.find('.form-field.error').removeClass('error');
                $form.find('.form-field small').remove();
                
                if( data.success ){
                    // yay!
                    $tab.html(data.html);
                    $('#enter-tab').trigger('sweeps.update');
                    if( Modernizr.touch ){
                        $(window).scrollTop($('#enter-tab').offset().top );
                    }
                    return;
                }
                
                if( data.age_restriction ){
                    // set the cookie because
                    $.cookie('sweep_age_restriction',1,{expires:1});
                    mask('age-restriction', 'age-restriction', 'Sorry, you are not eligible to enter.<div class="subtext">See <a href="#official-rules">Official Rules</a> below for details.</div>');
                    return;
                }
                
                // errors!
                for( var i in data.errors ){
                    $field = $form.find('#form-field-'+i);
                    $field.addClass('error');
                    $field.find('.controls').append(
                        $('<small>'+data.errors[i].pop()+'</small>')
                    );
                }
                
                // show the form has errors message.
                $form.find('.messages').html('<div class="alert-box error">Please correct the errors noted below.</div>');
                var t = $form.find('.messages').offset().top - 50;
                $(window).scrollTop(t);
                if( window.FB && window.FB.Canvas ) setTimeout(function(){ FB.Canvas.scrollTo(0, t); }, 100 );
                
            }).fail( function(){
                
            }).always( function(){
                $form_ct.removeClass('submitting');
                $submit.val($submitText).attr('disabled', false);
                setTimeout(resize, 10);
            });
        });
    })();
    
    (function init_tabs(){
        
        $('dl.tabs>dd>a').click(function(e){
            e.preventDefault();
            var active = $(this).closest('dl').find('a.active');
            if( active == $(this) ) return;
            active.removeClass('active');
            $(this).addClass('active');
            var id = $(this).attr('href')+'-tab';
            var tab = $(id);
            tab.closest('.tabs-content').children('li.active').removeClass('active');
            tab.addClass('active');
            // lets update the hash
            tab.attr('id', '');
            window.location.hash = id.replace(/\-tab$/, '');
            tab.attr('id', id.replace(/^#/, ''));
            resize();
            // bus.emit('tabchange');
        });
        
        // Check for the hash
        var onHashChange = function(){
            if( window.location.hash ) $('a[href="'+window.location.hash+'"]').click();
        };
        
        $(window).bind('hashchange', onHashChange);
        onHashChange();
        $(window).load(resize);
    })();
    
    (function official_rules_link(){
        $('a[href=#official-rules]').click(function(e){
            e.preventDefault();
            var t = $('#official-rules').offset().top;
            $(window).scrollTop(t);
            if( window.FB && window.FB.Canvas ) setTimeout(function(){ FB.Canvas.scrollTo(0, t); }, 100 );
        });
    })();
    
    
    
    (function facebook_connect(){
        if( !Sweep_Campaign.facebook_connect && !(Sweep_Campaign.like_gate == 2 && Sweep_Campaign.is_facebook_tab) ) return;
        if( !Sweep_Campaign.is_active ) return;
        if( $('#age-restriction, #inactive-after-end, #inactive-before-start').length ) return;
        
        FB.Event.subscribe('auth.statusChange', onStatusChange);
        FB.Event.subscribe('edge.create', checkLike);
        
        $(document).on('click','.facebook-login',function(){
            FB.login(function(){}, {scope:'email,user_likes'});
        });
        $(document).on('click','.facebook-logout',function(e){
            FB.logout(function(){ window.location.reload();});
        });
        
        function onStatusChange(data){
            
            switch( data.status ){
                case 'connected':
                    
                    if( $('.the-form form').length ){
                        $('<input type="hidden" name="fb_token" />')
                            .val(data.authResponse.accessToken)
                            .appendTo($('.the-form form'))
                            ;
                    }
                    
                    // update the mask
                    $('#facebook-connect-mask .facebook-login').remove();
                    $('#facebook-connect-mask .content-body').append($('<div class="subtext">Signing In...</div>'))
                    updateFields();
                    if( Sweep_Campaign.like_gate == 0 ) return;
                    checkLike();
                    updateLikeButtons();
                    break;
                case 'not_authorized':
                default:
                    
                    $('.facebook-user-block').html('');
                    mask('facebook-connect-mask',['facebook-connect-mask','facebook-mask'], 'Please login with Facebook.<br /><a class="facebook-login"><span>Login with Facebook</span></a>');
                    break;
            }
            
            updateLikeButtons();
        }
        
        function updateLikeButtons()
        {
            // if this is within 2 seconds of page load, don't reload...
            if( new Date().getTime() - _loadtime.getTime() < 1000 * 2 ) return;
            
            // find them
            $('.fb-like').html('').attr('class','fb-like').each(function(){
                var n = $(getLikeButtonHtml());
                $(this).after(n);
                $(this).remove();
                setTimeout(function(){FB.XFBML.parse();}, 10);
            });
        }
        
        function updateFields()
        {
            // check to see what we can fill out...
            FB.api('/me', function(data){
                // lets update fields if possible
                var autofill = ['first_name','last_name','name','email'];
                for(var i=0; i<autofill.length; i++){
                    var n = autofill[i]
                      , f = $('[name='+n+']');
                    if( !f.val() ) f.val(data[n]);
                }
                // update the facebook-user block
                $('.facebook-user-block').html([
                    '<img src="https://graph.facebook.com/'+data.id+'/picture?type=square" alt="" /> ',
                    'Logged in as '+data.name+', <a href="#" class="facebook-logout">Logout</a>'
                ].join(''));
            });
        }
        
        function checkLike()
        {
            FB.api('/fql', {
                q: 'SELECT page_id FROM page_fan WHERE uid=me() AND page_id = '+Sweep_Campaign.facebook_page_id
            }, function(response){
                
                if( response && response.data && response.data.length ){
                    if( Sweep_Campaign.onLiked ) Sweep_Campaign.onLiked(response.data);
                    else unmask();
                    return;
                }
                
                // else, we need to mask with the like button
                if( !Sweep_Campaign.is_facebook_tab) mask('facebook-connect-like-mask','facebook-mask', [
                    'Please like us on Facebook to enter!<br />',
                    getLikeButtonHtml()
                ], function(el){
                    FB.XFBML.parse(el[0]);
                });
                
                else{
                    if( !$('.active-mask').hasClass('facebook-like-mask') ){
                        window.location.reload();
                    }
                }
            });
        }
        
        function getLikeButtonHtml()
        {
            return ['<div class="fb-like-ct"><div class="fb-like" data-href="',
                    Sweep_Campaign.facebook_page_url,
                    '" data-send="false" data-width="320" data-show-faces="true"></div></div>'].join('');
        }
        
    })();
    
    (function share_buttons(){
        $(document).on('click', '.facebook-share', function(){
            var me = $(this);
            var share = {
                method          :'feed'
              , link            :window.location.href.replace(/facebook_tab\=1/, 'facebook_share=1')
              , picture         :me.attr('data-image') || $('meta[property="og:image"]').attr('content')
              , caption         :me.attr('data-caption') || $('meta[property="og:caption"]').attr('content')
              , description     :me.attr('data-description') || $('meta[property="og:description"]').attr('content')
              , name            :me.attr('data-title') || $('meta[property="og:title"]').attr('content')
            };
            
            FB.ui(share);
        });
    })();
    
    (function facebook_arrow(){
        var arrow = $('.facebook-arrow');
        if( !arrow.length ) return;
        
        (function animate()
        {
            var t = arrow.offset().top;
            arrow.animate({top: t < 5 ? 22 : 4}, 'slow', animate);
        })();
        
        
    })();
    
    function unmask()
    {
        $('body').removeClass('masked');
        $('.entry-mask').removeClass('active-mask');
    }
    
    function mask(id, classes, content, cb)
    {
        unmask();
        
        $('body').addClass('masked');
        
        if( $('#'+id).length ){
            $('#'+id).addClass('active-mask');
            return;
        }
        
        if( content instanceof Array ) content = content.join(' ');
        if( typeof classes == 'string' ) classes = [classes];
        classes[classes.length] = 'entry-mask active-mask';
        var el = $([
            '<div class="', classes.join(' '), '">',
                '<div class="bg" />',
                '<div class="content">',
                    '<div class="content-body">', content, '</div>',
                '</div>',
            '</div>'
        ].join('')).prependTo($('#enter-tab'));
        
        if( cb ) cb(el);
    }
    
    Sweeps.Campaign.unmask = unmask;
    Sweeps.Campaign.mask = mask;
    
    var _last_height = false;
    function resize()
    {
        var h;
        if( window.FB ) {
            if( !_last_height || _last_height != (h =$('.container').height())){
                _last_height = h;
                FB.Canvas.setSize({height:h});
            }
            
        }
    };
    
    setInterval(resize, 2000);
    
});
jQuery(function($){
    
    if( window.FB ) FB.init({
        appId      : Sweep_Campaign.facebook_id, // App ID
        channelUrl : Sweep_Campaign.channel_url, // Channel File
        status     : true, // check login status
        cookie     : true, // enable cookies to allow the server to access the session
        xfbml      : true  // parse XFBML
    });
    if( window.parent != window.self && Sweep_Campaign.is_facebook_tab == '1'){
        
        $('html,body').addClass('facebook-tab');
        
        if( window.FB ) FB.Canvas.scrollTo(0,0);
        function fixHeight(scroll){
            if( window.FB ) FB.Canvas.setSize({height: $('.wrapper').height()});
            if( scroll !== false )
                if( window.FB ) FB.Canvas.scrollTo(0,0);
        }
        
        jQuery('a[data-toggle="tab"]').on('shown', fixHeight);
        fixHeight();
        setTimeout(function(){ fixHeight(false); }, 1000);
        
        if( !Sweep_Campaign.is_facebook_liked ){
            $('nav, .tab-pane').hide();
            $('.like-us').show();
        }
    }
    
});
(function($){
    
    function bind(fn, scope)
    {
        return function(){
            return fn.apply(scope, arguments);
        };
    }
    
    var Field = function(settings)
    {
        this.el = false;
        this.settings = $.extend({
            'type'              :'textfield',
            'name'              :'Text Field',
            'options'           :false,
            'validators'        :[{
                'name'              :'notEmpty',
                'label'             :'Required Field'
            }]
        }, settings);
        
        this.type = this.settings.type;
        this.label = this.settings.label || this.settings.name;
        this.name = this.settings.name;
    };
    
    Field.id = 0;
    
    Field.prototype.getElement = function()
    {
        if( this.el ) return this.el;
        this.el = $([
            '<div class="element" data-type="',this.type,'">',
                
                '<div class="overview">',
                    '<div class="type-label in-right" />',
                    '<div class="tools in-right">',
                        '<a class="button edit">Edit</a>',
                        '<a class="button save button-primary">Save</a>',
                        '<a class="button remove">Remove</a>',
                    '</div>',
                    '<span class="icon" />',
                    '<span class="label" />',
                    '<input class="label" name="label" />',
                '</div>',
                
                '<div class="form">',
                    '<table class="admin-form">',
                        '<tr valign="top">',
                            '<th>Validators</th>',
                            '<td class="validators"></td>',
                        '</tr>',
                    '</table>',
                '</div>',
                
            '</div>'
        ].join(''));
        this.setElement(this.el);
        // initialize the label
        this.display.type.html( this.name );
        this.form.label.val( this.label );
        this.display.label.html( this.label );
        
        // initialize validators
        var $v = $('.validators'), $list;
        $v.html('');
        var validators = this.settings.validators;
        
        if( validators.length ){
            $list = $('<ul />').appendTo( $v );
        }
        
        for(var i=0; i<validators.length; i++ ){
            var v = validators[i];
            if(v) $list.append($('<li><label><input type="checkbox" name="'+v.name+'" value="1" /> '+v.label+'</label></li>'));
        }
        
        if( this.settings.options ){
            var f = this.el.find('.admin-form');
            f.append($('<tr valign="top"><th>Options</th><td><textarea name="options" /><span>Enter one option per line</span>'))
        }
        return this.el;
    };
    
    Field.prototype.getConfig = function()
    {
        var config = {
            type        :this.type,
            name        :this.name,
            label       :this.label,
            validators  :{}
        };
        
        var validators = this.settings.validators;
        for(var i=0; i<validators.length; i++){
            var v = validators[i];
            config.validators[v.name] = !!this.el.find('[name='+v.name+']:checked').length;
        }
        
        return config;
    };
    
    Field.prototype.setElement = function( el )
    {
        if( el.data('field') == this ) return;
        this.el = el;
        this.el.data('field', this);
        this.initElements();
        this.initEvents();
    }
    
    Field.prototype.copy = function()
    {
        return new Field(this.settings);
    };
    
    Field.prototype.initElements = function()
    {
        this.form = {
            'label'     : this.el.find('input[name=label]'),
            'validators': this.el.find('td.validators')
        };
        
        this.display = {
            'label'     : this.el.find('span.label'),
            'type'      : this.el.find('.type-label')
        };
        
        this.buttons = {
            'save'      : this.el.find('.button.save'),
            'remove'    : this.el.find('.button.remove'),
            'edit'      : this.el.find('.button.edit')
        };
        
    };
    
    Field.prototype.initEvents = function()
    {
        var self = this, i=0;
        this.display.label.click(function(){ self.toggleForm(); self.form.label.focus(); });
        this.buttons.save.click(function(){ self.toggleForm(); });
        this.buttons.edit.click(function(){ self.toggleForm(); });
        this.buttons.remove.click(function(){ self.remove(); });
    };
    
    Field.prototype.remove = function()
    {
        this.el.remove();
        if( this._onRemove ) this._onRemove(this);
    };
    
    Field.prototype.onRemove = function(cb)
    {
        this._onRemove = cb;
    };
    
    Field.prototype.toggleForm = function()
    {
        this.el.toggleClass('editing');
        if( !this.el.hasClass('editing') ){
            this.display.label.html( this.form.label.val() );
        }
    };
    
    
    // lets create the default field types
    var source = [
        new Field({type:'name',             name:'Name'}),
        new Field({type:'email',            name:'Email'}),
        new Field({type:'address',          name:'Address'}),
        new Field({type:'day',              name:'Birthday'}),
        new Field({type:'textfield',        name:'Text Field'}),
        new Field({type:'textarea',         name:'Text Area'}),
        new Field({type:'select',           name:'Select Box',      options:true}),
        new Field({type:'checkbox',         name:'Checkbox'}),
        new Field({type:'checkboxgroup',    name:'Checkbox Group',  options:true}),
        new Field({type:'radiogroup',       name:'Radio Group',     options:true})
    ];
    
    
    // the plugin
    $.fn.forminator = function(method){
        
        var input = this
          , config = []
          , currentDragField
          ;
        
        try{
            config = JSON.parse( this.val() );
        }
        catch(e){
            config = [];
        }
        
        // private methods
        function createElements()
        {
            var f = $('<div class="forminator" />').insertAfter(input)
              , l = $('<div class="left" />').appendTo(f)
              , r = $('<div class="right" />').appendTo(f)
              ;
            
            // initialize our form
            $.each(source, function(i, field){
                l.append( field.getElement() );
            });
            
            // setup drag and drop
            l.find('.element').draggable({
                
                connectToSortable:r,
                
                helper : function(e){
                    var el = $(e.target);
                    if( !el.hasClass('element') ) el = el.parents('.element');
                    currentDragField = el.data('field');
                    return currentDragField.getElement().clone();
                }
                
            });
            
            r.sortable({
                receive : function(e, ui){
                    var el = $(this).data('sortable').currentItem;
                    var field = currentDragField.copy();
                    field.setElement(el);
                    field.toggleForm();
                    field.onRemove(onFieldsUpdate);
                    field.form.label.focus();
                },
                
                update : onFieldsUpdate
            });
            
            input.data('forminator.elements', {l:l,r:r,f:f});
        }
        
        function onFieldsUpdate()
        {
            // update our input...
            var fields = [];
            input.data('forminator.elements').r.find('.element').each(function(i,el){
                fields[fields.length] = $(el).data('field').getConfig();
            });
            input.val( JSON.stringify( fields ) );
        }
        
        // public methods
        var methods = {
            init : function(){
                // lets remove the post box if we can
                var postbox = this.parents('.postbox');
                this.insertAfter(postbox);
                postbox.remove();
                createElements();
            }
        };
        
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            return $.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
        }
    };
    
})(jQuery);
function ComboBox(el) {
    this.dd = el;
    this.placeholder = this.dd.children('span');
    this.opts = this.dd.find('ul.dropdown > li');
    this.val = '';
    this.index = -1;
    this.initEvents();
    el.addClass("combobox");
    if (!ComboBox.hasDocClick)
    {
        ComboBox.hasDocClick = true;
        $(document).click(function() {
            $('.combobox').removeClass('active');
        });
    }
}
ComboBox.hasDocClick = false;
ComboBox.prototype = {
    initEvents : function() {
        var obj = this;

        obj.dd.on('click', function(event){
            $(this).toggleClass('active');
            return false;
        });

        obj.opts.on('click',function(){
            var opt = $(this);
            obj.val = opt.text();
            obj.index = opt.index();
            obj.placeholder.text(obj.val);
            obj.change();
        });
    },
    change: function (){},
    getValue : function() {
        return this.val;
    },
    getIndex : function() {
        return this.index;
    },
    setIndex: function (val)
    {
        this.index = val;
        this.placeholder.text($(this.opts[val]).text());
        this.change();
    },
    setValue: function(val) {
        this.val = val;
        this.placeholder.text(val);
        var activeIndex = -1;
        this.opts.each(function (index){
            if ($( this ).text() === val) activeIndex = index;
        });
        if (activeIndex !== -1) this.index = activeIndex;
        this.change();
    }
}

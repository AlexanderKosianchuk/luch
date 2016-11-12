function WindowFactory(window, document) {
    this.window = window;
    this.document = document;
    this.body = $("body");
    this.body.append("<div id='self' class='MoveMenu'></div>");
    this.moveMenu = $("div#self.MoveMenu");

    this.body.append("<div id='help' class='Help'>?</div>");
    this.help = $("div#help.Help");

    this.windowStack = new Array();

    this.topMenuHeight = 45;
    this.optionsMenuHeight = 45;
    this.leftMenuWidth = 210;

    $("#helpDialog").dialog({ autoOpen: false, width: 800, maxHeight:600 });
    this.help.on("click", function(){$("#helpDialog").dialog("open")});
}

WindowFactory.prototype.SupportMoveMenu = function() {
    var self = this;
    if(self.document.height() <= self.window.height()){
        self.moveMenu.css({
            "display" : "none"
        });
    } else if((self.document.height() > self.window.height() + 50) &&
            /* +50px becase there some deviations during ResizeContent() */
            (self.document.height() <= self.window.height() * 2)){
        self.moveMenu.empty();
        self.moveMenu.css({
            "display" : "block",
            "width" : "68px"
        });

        self.moveMenu.append("<div class='MoveMenuItem'>1</div>");
        self.moveMenu.append("<div class='MoveMenuItem'>2</div>");
    }  else if((self.document.height() > self.window.height() * 2 + 50) &&
            (self.document.height() <= self.window.height() * 3)){
        self.moveMenu.empty();
        self.moveMenu.css({
            "display" : "block",
            "width" : "102px"
        });

        self.moveMenu.append("<div class='MoveMenuItem'>1</div>");
        self.moveMenu.append("<div class='MoveMenuItem'>2</div>");
        self.moveMenu.append("<div class='MoveMenuItem'>3</div>");
    }

    self.moveMenu.on("click", '*', function(e){
        var contex = $(e.target);
        if(contex.html() == "1"){
            self.window.scrollTop(0);
        } else if(contex.html() == "2"){
            self.window.scrollTop(self.window.height());
        } else if(contex.html() == "3"){
            self.window.scrollTop(self.window.height() * 2);
        }
    });
};

WindowFactory.prototype.NewShowcase = function() {
    var self = this,
        maxIndex = 0;

    for(var i = 0; i < self.windowStack.length; i++){
        var curIndex = $(self.windowStack[i]).data('index');
        if(maxIndex <= curIndex){
            maxIndex = curIndex + 1;
        }
    }

    self.body.append("<div id='factoryWindow"+maxIndex+"' class='FactoryWindow' " +
            "data-index='"+ maxIndex +"'></div>");
    var showcase = $("div#factoryWindow"+maxIndex);
    showcase.css({
        'top': self.window.height() * self.windowStack.length,
        'height': self.window.height(),
        'width': self.window.width()
    });
    self.windowStack.push(showcase);

    return showcase;
};

WindowFactory.prototype.RemoveShowcases = function(level) {
    var self = this;
    for(var i = 0; i < self.windowStack.length; i++){
        var showcase = $(self.windowStack[i]),
            index = showcase.data('index');
        if(index >= level){
            showcase.remove();
        }
    }

    self.windowStack = $(".FactoryWindow");
    return true;
};

WindowFactory.prototype.RemoveShowcase = function(showcase) {
    var self = this,
        showcase = $(showcase),
        index = showcase.data('index');

    showcase.fadeOut(500, function(e){
        showcase.remove();
        self.windowStack.splice(index, 1);
        self.document.scrollTop((index - 1) * self.window.height());
    });
};

WindowFactory.prototype.ClearShowcase = function(showcase) {
    var self = this;
    showcase.empty();
};

WindowFactory.prototype.ResizeShowcase = function(e) {
    var self = this;

    var FW = $(".FactoryWindow");
    FW.css({
        "height": self.window.height(),
        "width": self.window.width(),
    });

    $.each(FW, function(i, item){
        var item = $(item);
        item.css('top', i * self.window.height());
    });

    $(".LeftMenu").css("height", self.window.height() - self.topMenuHeight);
    $(".WorkSpace").css({
        "height": self.window.height() - self.topMenuHeight - 10, //10 because padding
        "width": self.window.width() - self.leftMenuWidth,
    });
    $(".OptionsMenu").css("width", self.window.width() - self.leftMenuWidth - 20);
    $(".Content").css({
        "height": self.window.height() - self.optionsMenuHeight - self.topMenuHeight - 35, //35 because padding and margin
        "width": self.window.width() - self.leftMenuWidth
    });

    $(".OptionsMenuFullWidth").css("width", self.window.width() - 10);
    $(".ContentFullWidth").css({
        "height": self.window.height() - self.optionsMenuHeight - self.topMenuHeight - 35, //35 because padding and margin
        "width": self.window.width() - 10
    });

    $(".ContentFullSize").css({
        "height": self.window.height() - self.topMenuHeight - 35, //35 because padding and margin
        "width": self.window.width() - 10
    });

    self.SupportMoveMenu();
};

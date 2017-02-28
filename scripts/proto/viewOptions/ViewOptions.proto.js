function FlightViewOptions(window, document, langStr, eventHandler)
{
    this.langStr = langStr;

    this.flightId = null;
    this.task = null;

    this.window = window;
    this.document = document;

    this.eventHandler = eventHandler;
    this.flightOptionsFactoryContainer = null;
    this.flightOptionsTopMenu = null;
    this.flightOptionsLeftMenu = null;
    this.flightOptionsWorkspace = null;
    this.flightOptionsOptions = null;
    this.flightOptionsContent = null;

    this.rangeSlider = null;
}

FlightViewOptions.prototype.FillFactoryContaider = function(factoryContainer) {
    var self = this;
    this.flightOptionsFactoryContainer = factoryContainer;

    var pV = {
            action: "viewOptions/putViewOptionsContainer",
            data: {
                data: 'data'
            }
    };

    $.ajax({
        type: "POST",
        data: pV,
        dataType: 'json',
        url: ENTRY_URL,
        async: true
    }).fail(function(msg){
        console.log(msg);
    }).done(function(answ) {
        if(answ["status"] == "ok") {
            var data = answ['data'];

            self.flightOptionsFactoryContainer.append(data['topMenu']);
            self.flightOptionsFactoryContainer.append(data['leftMenu']);
            self.flightOptionsFactoryContainer.append(data['workspace']);

            self.flightOptionsTopMenu = $('div#topMenuOptionsView');

            self.flightOptionsLeftMenu = $('div#leftMenuOptionsView');
            self.flightOptionsLeftMenu.on("click", function(e){
                self.leftMenuClick(e);
            });

            self.flightOptionsWorkspace = $('div#flightOptionsWorkspace');

            if(self.task == null){
                self.ShowFlightViewTemplates();
            } else if(self.task === 'getBruTemplates'){
                $("#leftMenuOptionsView .LeftMenuRowSelected").removeClass('LeftMenuRowSelected');
                $("#templatesLeftMenuRow").addClass('LeftMenuRowSelected');

                self.ShowFlightViewTemplates();
            } else if(self.task === 'getEventsList'){
                $("#leftMenuOptionsView .LeftMenuRowSelected").removeClass('LeftMenuRowSelected');
                $("#eventsLeftMenuRow").addClass('LeftMenuRowSelected');

                self.ShowFlightViewEvents();
            } else if(self.task === 'getParamList'){
                $("#leftMenuOptionsView .LeftMenuRowSelected").removeClass('LeftMenuRowSelected');
                $("#paramsListLeftMenuRow").addClass('LeftMenuRowSelected');

                self.ShowFlightViewParamsList();
            }

            self.ResizeFlightViewOptionsContainer();
            self.document.scrollTop(factoryContainer.data("index") * self.window.height());

        } else {
            console.log(answ["error"]);
        }
    });
}

FlightViewOptions.prototype.ShowFlightViewTemplates = function() {
    if(this.flightOptionsWorkspace.html() != ''){
        this.flightOptionsWorkspace.empty();
    }

    this.ShowTopMenuTempltListButtons();
    this.ShowFlightViewTempltListOptions();
    this.ShowTempltList();
}

FlightViewOptions.prototype.ShowFlightViewEvents = function() {
    if(this.flightOptionsWorkspace.html() != ''){
        this.flightOptionsWorkspace.empty();
    }

    this.ShowTopMenuEventsListButtons();
    this.ShowFlightViewEventsListOptions();
    this.ShowEventsList();
    this.SupportUserComment();
}

FlightViewOptions.prototype.ShowFlightViewParamsList = function() {
    if(this.flightOptionsWorkspace.html() != ''){
        this.flightOptionsWorkspace.empty();
    }

    this.ShowTopMenuParamsListButtons();
    this.ShowFlightViewParamsListOptions();
    this.ShowParamList();
}

FlightViewOptions.prototype.ResizeFlightViewOptionsContainer = function(e) {
    var self = this;
    self.eventHandler.trigger("resizeShowcase");
    return false;
}

///====================================================
//
///====================================================
FlightViewOptions.prototype.leftMenuClick = function(e){
    var self = this,
    target = $(e.target);
    e.stopPropagation();

    if(target.attr('id') == "templatesLeftMenuRow"){
        if(!target.hasClass('LeftMenuRowSelected')){
            $("#leftMenuOptionsView .LeftMenuRowSelected")
                .removeClass('LeftMenuRowSelected');

            target.addClass('LeftMenuRowSelected');
            $("#leftMenuOptionsView .SearchBox").prop('disabled', true);

            self.ShowFlightViewTemplates();
        }
    } else if(target.attr('id') == "eventsLeftMenuRow"){
        if(!target.hasClass('LeftMenuRowSelected')){
            $("#leftMenuOptionsView .LeftMenuRowSelected")
                .removeClass('LeftMenuRowSelected');

            target.addClass('LeftMenuRowSelected');
            $("#leftMenuOptionsView .SearchBox").prop('disabled', true);

            self.ShowFlightViewEvents();
        }
    } else if(target.attr('id') == "paramsListLeftMenuRow"){
        if(!target.hasClass('LeftMenuRowSelected')){
            $("#leftMenuOptionsView .LeftMenuRowSelected")
                .removeClass('LeftMenuRowSelected');

            target.addClass('LeftMenuRowSelected');
            $("#leftMenuOptionsView .SearchBox").prop('disabled', true);

            self.ShowFlightViewParamsList();
        }
    }
}

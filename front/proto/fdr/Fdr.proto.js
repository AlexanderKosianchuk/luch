function Fdr(window, document, langStr, eventHandler)
{
    var langStr = langStr,

    window = window;
    document = document;

    this.bruTypeId = null;
    this.task = null;
    this.eventHandler = eventHandler;
    this.bruTypeListFactoryContainer = null;

    ///
    // PRIVATE
    ///
    var that = this,
        bruTypeId = null,
        bruTypeListTopMenu = null,
        bruTypeListLeftMenu = null,
        bruTypeListWorkspace = null;

    var GeneralInfo = null,
        Templates = null;

    var LeftMenuClick = function(e) {
        var target = $(e.target);

        if(target.attr('id') == "editBruGeneralInfoLeftMenuRow"){
            if(!target.hasClass('LeftMenuRowSelected')){
                $("#leftMenuBruType .LeftMenuRowSelected").removeClass('LeftMenuRowSelected', {duration:500});

                target.addClass('LeftMenuRowSelected', {duration:500});

                if(GeneralInfo == null){
                    GeneralInfo = new FdrGeneralInfo(langStr, eventHandler, that.bruTypeListFactoryContainer);
                };

                GeneralInfo.Show(bruTypeId, bruTypeListTopMenu, bruTypeListWorkspace);
            }
        } else if(target.attr('id') == "editBruTplsLeftMenuRow"){
            if(!target.hasClass('LeftMenuRowSelected') &&
                    (Templates != null)){
                $("#leftMenuBruType .LeftMenuRowSelected").removeClass('LeftMenuRowSelected', {duration:500});

                target.addClass('LeftMenuRowSelected', {duration:500});

                if(Templates == null){
                    Templates = new FdrTemplates(langStr, that.eventHandler, that.bruTypeListFactoryContainer);
                };

                Templates.Show(bruTypeId, bruTypeListTopMenu, bruTypeListWorkspace);
            }
        } else if(target.attr('id') == "editBruCycloLeftMenuRow"){
            if(!target.hasClass('LeftMenuRowSelected')){
                $("#leftMenuBruType .LeftMenuRowSelected").removeClass('LeftMenuRowSelected', {duration:500});

                target.addClass('LeftMenuRowSelected', {duration:500});

                //self.ShowFlightViewParamsList();
            }
        } else if(target.attr('id') == "editBruEventsLeftMenuRow"){
            if(!target.hasClass('LeftMenuRowSelected')){
                $("#leftMenuBruType .LeftMenuRowSelected").removeClass('LeftMenuRowSelected', {duration:500});

                target.addClass('LeftMenuRowSelected', {duration:500});

                //self.ShowFlightViewParamsList();
            }
        }

        return this;
    }

    ///
    // PRIVILEGED
    ///

    this.ResizeBruTypeContainer = function(e) {
        that.eventHandler.trigger("resizeShowcase");
        return this;
    };

    this.FillFactoryContaider = function(factoryContainer) {
        var self = this,
            task = this.task;

        bruTypeId = this.bruTypeId;

        self.bruTypeListFactoryContainer = factoryContainer;

        $.ajax({
            type: "POST",
            data: {
                action: 'fdr/putBruTypeContainer',
                data: 'data'
            },
            dataType: 'json',
            url: ENTRY_URL,
            async: true
        }).fail(function(msg){
            console.log(msg);
        }).done(function(answ) {
            if(answ["status"] == "ok") {
                var data = answ['data'];

                self.bruTypeListFactoryContainer.append(data['topMenu']);
                self.bruTypeListFactoryContainer.append(data['leftMenu']);
                self.bruTypeListFactoryContainer.append(data['workspace']);

                bruTypeListTopMenu = $('div#topMenuBruType');

                bruTypeListLeftMenu = $('div#leftMenuBruType');
                bruTypeListLeftMenu.on("click", function(e){
                    LeftMenuClick(e);
                });

                bruTypeListWorkspace = $('div#bruTypeWorkspace');

                if(task == null){
                    $("#editBruGeneralInfoLeftMenuRow").addClass("LeftMenuRowSelected");

                    if(bruTypeListWorkspace.html() != ''){
                        bruTypeListWorkspace.empty();
                    }

                    GeneralInfo = new FdrGeneralInfo(langStr, that.eventHandler, that.bruTypeListFactoryContainer);
                    GeneralInfo.Show(bruTypeId, bruTypeListTopMenu, bruTypeListWorkspace);

                } else if(task == 'editingBruTypeGeneralInfo'){
                    $("#editBruGeneralInfoLeftMenuRow").addClass("LeftMenuRowSelected");

                    if(bruTypeListWorkspace.html() != ''){
                        bruTypeListWorkspace.empty();
                    }

                    var GeneralInfo = new FdrGeneralInfo(langStr, that.eventHandler, that.bruTypeListFactoryContainer);
                    GeneralInfo.Show(bruTypeId, bruTypeListTopMenu, bruTypeListWorkspace);

                } else if(task == 'editingBruTypeTemplates'){
                    $("#editBruTplsLeftMenuRow").addClass("LeftMenuRowSelected");

                    Templates = new FdrTemplates(langStr, that.eventHandler, that.bruTypeListFactoryContainer);
                    Templates.Show(bruTypeId, bruTypeListTopMenu, bruTypeListWorkspace);
                }

                self.ResizeBruTypeContainer();

            } else {
                console.log(answ["error"]);
            }
        });
    };
}

Fdr.prototype.copyTemplate = function(flightId, tplName) {
    var $dfd = $.Deferred()
        that = this;

    $.post(
        ENTRY_URL,
        {
            action: 'fdr/copyTemplate',
            data: {
                flightId: flightId,
                tplName: tplName
            }
        },
        function (responce) {
            var data = [null, //flightId null to leave curr val
                'getBruTemplates',
                that.bruTypeListFactoryContainer
            ];

            that.eventHandler.trigger("viewFlightOptions", data);

            $dfd.resolve();
        }
    ).fail(function() {
        $dfd.resolve();
    });

    return $dfd.promise();
};

module.exports = Fdr;

var BRU_SRC = location.protocol + '//' + location.host + "/view/bru.php";

function BruType(window, document, langStr, srvcStrObj, eventHandler)
{
    var langStr = langStr,
        srvcStrObj = srvcStrObj,
        actions = srvcStrObj["bruTypesPage"],
        flightViewOptionActions = srvcStrObj["viewOptionsPage"];

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
                    GeneralInfo = new BruTypeGeneralInfo(langStr, srvcStrObj, eventHandler, that.bruTypeListFactoryContainer);
                };

                GeneralInfo.Show(bruTypeId, bruTypeListTopMenu, bruTypeListWorkspace);
            }
        } else if(target.attr('id') == "editBruTplsLeftMenuRow"){
            if(!target.hasClass('LeftMenuRowSelected') &&
                    (Templates != null)){
                $("#leftMenuBruType .LeftMenuRowSelected").removeClass('LeftMenuRowSelected', {duration:500});

                target.addClass('LeftMenuRowSelected', {duration:500});

                if(Templates == null){
                    Templates = new BruTypeTemplates(langStr, srvcStrObj, that.eventHandler, that.bruTypeListFactoryContainer);
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

        var pV = {
                action: actions["putBruTypeContainer"],
                data: {
                    data: 'data'
                }
        };

        $.ajax({
            type: "POST",
            data: pV,
            dataType: 'json',
            url: BRU_SRC,
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

                    GeneralInfo = new BruTypeGeneralInfo(langStr, srvcStrObj, that.eventHandler, that.bruTypeListFactoryContainer);
                    GeneralInfo.Show(bruTypeId, bruTypeListTopMenu, bruTypeListWorkspace);

                } else if(task == actions['editingBruTypeGeneralInfo']){
                    $("#editBruGeneralInfoLeftMenuRow").addClass("LeftMenuRowSelected");

                    if(bruTypeListWorkspace.html() != ''){
                        bruTypeListWorkspace.empty();
                    }

                    var GeneralInfo = new BruTypeGeneralInfo(langStr, srvcStrObj, that.eventHandler, that.bruTypeListFactoryContainer);
                    GeneralInfo.Show(bruTypeId, bruTypeListTopMenu, bruTypeListWorkspace);

                } else if(task == actions['editingBruTypeTemplates']){
                    $("#editBruTplsLeftMenuRow").addClass("LeftMenuRowSelected");

                    Templates = new BruTypeTemplates(langStr, srvcStrObj, that.eventHandler, that.bruTypeListFactoryContainer);
                    Templates.Show(bruTypeId, bruTypeListTopMenu, bruTypeListWorkspace);
                }

                self.ResizeBruTypeContainer();

            } else {
                console.log(answ["error"]);
            }
        });
    };
}

BruType.prototype.copyTemplate = function(flightId, tplName) {
    var $dfd = $.Deferred()
        that = this;

    $.post(
        BRU_SRC,
        {
            action: 'copyTemplate',
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

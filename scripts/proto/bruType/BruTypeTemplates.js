///==================================================
//TEMPLATES
///==================================================
function BruTypeTemplates(langStr, eventHandler, bruTypeListFactoryContainer) {
    var langStr = langStr

    var bruTypeId = null;

    var factoryWindow = bruTypeListFactoryContainer,
        bruTypeTopMenu = null,
        bruTypeListWorkspace = null,
        bruTypeListOptions = null,
        bruTypeListContent = null;

    ///
    // PRIVATE
    ///
    var ShowTemplatesOptions = function() {
        bruTypeListWorkspace.append("<div id='bruTypeOptions' class='OptionsMenu'></div>");
        bruTypeListOptions = $("div#bruTypeOptions");

        var fligthOptionsStr = '<table v-align="top"><tr>' +
            '<td><label>' + langStr.bruTypeTplActions + " - " + '</label></td><td>' +
            '<div>' +
                '<button id="applyEditingBruTypeTplBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.applyEditingTpl + '</button>' +
            '</div>' +
            '</td><td>' +
            '<div>' +
                '<button id="defaultBruTypeTplBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.defaultTpl + '</button>' +
            '</div>' +
            '</td><td>' +
            '<div>' +
                '<button id="deleteBruTypeTplBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.deleteTpl + '</button>' +
            '</div>' +
            '</td><td>' +
            '<div>' +
                '<button id="clearBruTypeTplBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.clearTplPatamsList + '</button>' +
            '</div>' +
            '</td></tr></table>';

        bruTypeListOptions.append(fligthOptionsStr);
        SupportTemplatesOptionsButt();

        $("div#bruTypeOptions .Button").button();
    },

    SupportTemplatesOptionsButt = function(){
        $("button#applyEditingBruTypeTplBut").on("click", function(e){
            var bruTypeNewTemplateName = $("#bruTypeNewTemplateName").val().trim(),
                tplOldName$ = $("#bruTypeTplList option:selected").attr("name").trim(),
                params = $(".ParamsCheckboxGroup:checked");

            if(bruTypeNewTemplateName.length == 0){
                alert(langStr.tplNameCantBeBlank);
            } else if (params.length == 0) {
                alert(langStr.tplCantBeEmpty);
            } else {
                var paramCodes = [];

                params.each(function(index, item) {
                    paramCodes.push($(item).val());
                });

                var pV = {
                        action: "updateTpl",
                        data: {
                            bruTypeId: bruTypeId,
                            tplOldName: tplOldName$,
                            name: bruTypeNewTemplateName,
                            params: paramCodes
                        }
                };

                $.ajax({
                    type: "POST",
                    data: pV,
                    datatype: 'json',
                    url: BRU_SRC, //defined as global in BruType.proto
                    async: true
                }).fail(function(msg){
                    console.log(msg);
                }).done(function(e){
                    ReceiveTplsList()
                        .done(function(answ) {
                            if(answ["status"] == "ok") {
                                var data = answ["data"],
                                    bruTypeTplList = $("#bruTypeTplList");
                                bruTypeTplList.empty();
                                bruTypeTplList.append(data['bruTypeTpls']);
                                SupportTplsListClick();
                            } else {
                                console.log(answ["error"]);
                            }
                        });
                });
            }
        });

        $("button#deleteBruTypeTplBut").on("click", function(e){
            var tplName$ = $("#bruTypeTplList option:selected").attr("name").trim(),
                pV = {
                        action: "deleteTpl",
                        data: {
                            bruTypeId: bruTypeId,
                            name: tplName$
                        }
                };

            $.ajax({
                type: "POST",
                data: pV,
                datatype: 'json',
                url: BRU_SRC, //defined as global in BruType.proto
                async: true
            }).fail(function(msg){
                console.log(msg);
            }).done(function(e){
                $(".ParamsCheckboxGroup").prop("checked", false);

                ReceiveTplsList()
                .done(function(answ) {
                    if(answ["status"] == "ok") {
                        var data = answ["data"],
                            bruTypeTplList = $("#bruTypeTplList");
                        bruTypeTplList.empty();
                        bruTypeTplList.append(data['bruTypeTpls']);
                        SupportTplsListClick();
                    } else {
                        console.log(answ["error"]);
                    }
                });
            });
        });

        $("button#defaultBruTypeTplBut").on("click", function(e){
            var tplName$ = $("#bruTypeTplList option:selected").attr("name").trim(),
            pV = {
                    action: "defaultTpl",
                    data: {
                        bruTypeId: bruTypeId,
                        name: tplName$
                    }
            };

            $.ajax({
                type: "POST",
                data: pV,
                datatype: 'json',
                url: BRU_SRC, //defined as global in BruType.proto
                async: true
            }).fail(function(msg){
                console.log(msg);
            }).done(function(e){
                ReceiveTplsList()
                    .done(function(answ) {
                        if(answ["status"] == "ok") {
                            var data = answ["data"],
                                bruTypeTplList = $("#bruTypeTplList");
                            bruTypeTplList.empty();
                            bruTypeTplList.append(data['bruTypeTpls']);
                            SupportTplsListClick();
                        } else {
                            console.log(answ["error"]);
                        }
                    });
            });
        });

        $("button#clearBruTypeTplBut").on("click", function(e){
            $("#bruTypeNewTemplateName").val("");
            $("#bruTypeTplList option:selected").prop("selected", false);
            $(".ParamsCheckboxGroup").prop("checked", false);
        });
    }

    ShowTopMenu = function(){
        if(bruTypeTopMenu != null){
            if(bruTypeTopMenu.html() != ''){
                bruTypeTopMenu.empty();
            }

            bruTypeTopMenu.append('<label id="here" class="HereRight">' +
                    '<span style="position:absolute; margin-top:8px;">&nbsp;' +
                    langStr.bruTypeTplsToViewOptions +
                    '</span>' +
                '</label>');

            $("#here").on("click", function(e){
                var data = [null, //flightId null to leave curr val
                    'getBruTemplates',
                    factoryWindow];

                eventHandler.trigger("viewFlightOptions", data);
            });
        }
    },

    AddNewTemplateCtrls = function(container){
        var createTemplateCtrl = $("<div></div>")
        .addClass("CreateTemplateCtrl")
        .appendTo(container);

        var center = $("<center></center>")
        .appendTo(createTemplateCtrl);

        $("<input/>")
        .addClass("BruTypeTemplatesCtrl")
        .attr("id", "bruTypeNewTemplateName")
        .appendTo(center);

        $("<button></button>")
        .addClass("BruTypeTemplatesCtrl")
        .attr("id", "bruTypeNewTemplateCreateButt")
        .appendTo(center)
        .text(langStr.createTpl)
        .button()
        .on("click", function(e){
            e.preventDefault();
            var bruTypeNewTemplateName = $("#bruTypeNewTemplateName").val().trim(),
                params = $(".ParamsCheckboxGroup:checked");

            if(bruTypeNewTemplateName.length == 0){
                alert(langStr.tplNameCantBeBlank);
            } else if (params.length == 0) {
                alert(langStr.tplCantBeEmpty);
            } else {
                var paramCodes = [];
                params.each(function(index, item) {
                    paramCodes.push($(item).val());
                });

                CreateTpl(bruTypeNewTemplateName, paramCodes)
                    .done(function(answ) {
                        if(answ["status"] == "ok") {
                            ReceiveTplsList()
                                .done(function(answ) {
                                    if(answ["status"] == "ok") {
                                        var data = answ["data"],
                                            bruTypeTplList = $("#bruTypeTplList");
                                        bruTypeTplList.empty();
                                        bruTypeTplList.append(data['bruTypeTpls']);
                                        SupportTplsListClick();
                                    } else {
                                        console.log(answ["error"]);
                                    }
                                });
                        } else {
                            console.log(answ["error"]);
                        }
                    });
            }
        });
    },

    CreateTpl = function(tplName, params){
        var pV = {
                action: "createTpl",
                data: {
                    bruTypeId: bruTypeId,
                    name: tplName,
                    params: params
                }
        };

        return $.ajax({
            type: "POST",
            data: pV,
            dataType: 'json',
            url: BRU_SRC, //defined as global in BruType.proto
            async: true
        }).fail(function(msg){
            console.log(msg);
        });
    },

    ReceiveTplsList = function(){
        var pV = {
                action: "editingBruTypeTemplatesReceiveTplsList",
                data: {
                    bruTypeId: bruTypeId
                }
        };

        return $.ajax({
            type: "POST",
            data: pV,
            dataType: 'json',
            url: BRU_SRC, //defined as global in BruType.proto
            async: true
        }).fail(function(msg){
            console.log(msg);
        });
    },

    SupportTplsListClick = function(){
        $("#bruTypeTplList option").on("click", function(e){
            var this$ = $(this),
                params = this$.data("params").split(","),
                paramsCheckboxGroup = $(".ParamsCheckboxGroup");
            paramsCheckboxGroup.prop("checked", false);
            for(var i = 0; i < params.length; i++){
                var paramCode = (params[i]).trim();
                paramsCheckboxGroup.filter("[value='" + paramCode + "']").prop("checked", true);
            }

            $("#bruTypeNewTemplateName").val(this$.attr("name"));
        });
    },

    ReceiveParamsList = function(){
        var pV = {
                action: "editingBruTypeTemplatesReceiveParamsList",
                data: {
                    bruTypeId: bruTypeId
                }
        };

        return $.ajax({
            type: "POST",
            data: pV,
            dataType: 'json',
            url: BRU_SRC, //defined as global in BruType.proto
            async: true
        }).fail(function(msg){
            console.log(msg);
        });
    },

    ShowTemplatesContent = function() {
        if(bruTypeId != null){
            bruTypeListContent = $("<div></div>")
                .addClass('Content')
                .attr('id','bruTypeTemplatestContent')
                .appendTo(bruTypeListWorkspace);

            var bruTypeTplList = $("<select></select>")
                .attr("id", "bruTypeTplList")
                .attr("size", "160")
                .attr("multiple", false)
                .addClass("BruTypeTplListSelect")
                .addClass("is-scrollable")
                .appendTo(bruTypeListContent);

            AddNewTemplateCtrls(bruTypeListContent);

            $("<div></div>")
                .attr("id", "bruTypeTplParamsListContainer")
                .addClass("TemplatesParamsListContainer")
                .addClass("is-scrollable")
                .appendTo(bruTypeListContent);

            ReceiveTplsList()
                .done(function(answ) {
                    if(answ["status"] == "ok") {
                        var data = answ["data"];
                        bruTypeTplList.append(data['bruTypeTpls']);
                        SupportTplsListClick();
                    } else {
                        console.log(answ["error"]);
                    }
                });

            ReceiveParamsList()
                .done(function(answ) {
                    if(answ["status"] == "ok") {
                        var data = answ["data"],
                        /*pure js to accelerate html appending*/
                            bruTypeTplParamsListContainer = document.getElementById('bruTypeTplParamsListContainer');
                        bruTypeTplParamsListContainer.innerHTML = data['bruTypeParams'];
                    } else {
                        console.log(answ["error"]);
                    }
                });

        }

        return false;
    };


    ///
    // PRIVILEGED
    ///

    this.Show = function(extBruTypeId, extBruTypeTopMenu, extBruTypeListWorkspace) {
        bruTypeId = extBruTypeId;
        bruTypeTopMenu = extBruTypeTopMenu;
        bruTypeListWorkspace = extBruTypeListWorkspace;

        bruTypeListWorkspace.empty();

        ShowTemplatesOptions();
        ShowTopMenu();
        ShowTemplatesContent();
    };
};

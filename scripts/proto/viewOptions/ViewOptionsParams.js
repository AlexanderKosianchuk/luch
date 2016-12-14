///====================================================
//PARAM LIST
///====================================================
FlightViewOptions.prototype.ShowTopMenuParamsListButtons = function(){
    var self = this;
    if(self.flightOptionsTopMenu != null){
        if(self.flightOptionsTopMenu.html() != ''){
            self.flightOptionsTopMenu.empty();
        }

        self.flightOptionsTopMenu.append('<label id="down" class="Down">' +
                '<span style="position:absolute; margin-top:8px;">&nbsp;' +
                self.langStr.flightViewOptionsShow +
                '</span>' +
                '</label>');

        $("#down").on("click", function(e){
            if(self.flightOptionsLeftMenu != null){
                //check out what left menu option selected
                var currentOption = self.flightOptionsLeftMenu .find(".LeftMenuRowSelected").attr('id');

                if(currentOption = "paramsListLeftMenuRow"){
                    var tplName = "last",
                    checkedItems = $(".ParamsCheckboxGroup:checked");

                    var params = new Array();
                    $.each(checkedItems, function(index, item){
                        var item = $(item);
                        params.push(item.attr("value"));
                    });
                    params = params.getUnique();

                    self.TplCreate(self.flightId, tplName, params).done(function(answ){
                        if(answ["status"] == "ok") {
                            var apParams = answ['data']['ap'],
                                bpParams = answ['data']['bp'],

                                startCopyTime = self.rangeSlider.data("startcopytime"),
                                stepLength = self.rangeSlider.data("steplength"),
                                startFrame = self.rangeSlider.slider("values", 0) / stepLength,
                                endFrame = self.rangeSlider.slider("values", 1) / stepLength;

                            var data = [self.flightId, tplName,
                                    stepLength, startCopyTime,
                                    startFrame, endFrame,
                                    apParams, bpParams];

                            self.eventHandler.trigger("showChart", data);

                        } else {
                            console.log(answ["error"]);
                        }
                    });
                }
            }
        });
    }
}

FlightViewOptions.prototype.ShowFlightViewParamsListOptions = function() {
    //MainContainerUploaderOptions
    var self = this,
    flightId = self.flightId;

    if(flightId != null){
        var viewOptionsStr = "<div id='flightOptionsOptions' class='OptionsMenu'>" +
            "<table v-align='top' style='width:800px;'><tr>" +
                    "<td>" +
                        "<label>" + self.langStr.flightViewOptionsTimeRange + " - " + "</label>" +
                    "</td>" +
                    "<td style='width:250px; padding:6px 15px 0px 0px;'>" +
                        "<div id='sliderRange' style='font-size:14px;'></div>" + //font size for slider
                    "</td>" +
                    "<td style='padding-top:4px;'>" +
                        "<input type='text' id='amount' readonly style='width:220px; border:0; font-size:24px; color: #585858;'>" +
                    "</td>" +
                    "<td style='padding-top:4px;'>" +
                        "<div id='saveTplFromParams' class='Button'>"+ self.langStr.saveParamsAsTpl + "</div>" +
                    "</td>" +
                    "<td style='padding-top:8px;'>" +
                        "<img id='saveTplProgress' class='SmallProgressImg' src='stylesheets/basicImg/loading.gif'/>" +
                    "</td>" +
            "</td></tr></table></div>";

        self.flightOptionsWorkspace.append(viewOptionsStr);
        self.flightOptionsOptions = $("div#flightOptionsOptions");

        $("#saveTplFromParams")
            .button()
            .on("click", function(e){
                var d = new Date(),
                    currDate = d.getDate(),
                    currMonth = d.getMonth() + 1,
                    currYear = d.getFullYear(),
                    currHour = d.getHours(),
                    currMin = d.getMinutes(),
                    currSec = d.getSeconds(),
                    tplName = (currDate + "-" + currMonth + "-" + currYear + " " +
                            currHour + ":" + currMin + ":" + currSec),
                    checkedItems = $(".ParamsCheckboxGroup:checked");

                var params = new Array();
                $.each(checkedItems, function(index, item){
                    var item = $(item);
                    params.push(item.attr("value"));
                });
                params = params.getUnique();

                if(params.length > 0){
                    $("#saveTplProgress").css('visibility', 'visible');
                    self.TplCreate(flightId, tplName, params).done(function(e){
                        $("#saveTplProgress").css('visibility', 'hidden');
                    });
                }
            });

        var pV = {
                action: self.actions["getFlightDuration"],
                data: {
                    flightId: flightId
                }
        };

        $.ajax({
            type: "POST",
            data: pV,
            dataType: 'json',
            url: FLIGHTS_VIEW_OPTIONS_SRC,
            async: true
        }).fail(function(msg){
            console.log(msg);
        }).done(function(answ) {
            if(answ["status"] == "ok") {
                var duration = answ['data']['duration'],
                startCopyTime = answ['data']['startCopyTime'],
                stepLength = answ['data']['stepLength'];
                //slider

                self.rangeSlider = $("div#sliderRange").slider({
                      range: true,
                      min: 0,
                      max: duration,
                      values: [0, duration],
                      slide: function(event, ui) {
                          $("#amount").val(ui.values[0].toString().toHHMMSS() + " - " +
                                ui.values[1].toString().toHHMMSS());
                      },
                      change: function( event, ui ) {
                          $("#amount").val(ui.values[0].toString().toHHMMSS() + " - " +
                                    ui.values[1].toString().toHHMMSS());
                      }
                });

                //set initial full slider range
                var amount = $("#amount").val(
                    self.rangeSlider.slider("values", 0).toString().toHHMMSS() +
                    " - " +
                    self.rangeSlider.slider("values", 1).toString().toHHMMSS()
                );

                self.rangeSlider.data("startcopytime", startCopyTime);
                self.rangeSlider.data("steplength", stepLength);

            } else {
                console.log(answ["error"]);
            }
        });
    }
}

FlightViewOptions.prototype.ShowParamList = function() {
    var self = this,
        flightId = self.flightId,
        viewOptionsDataContainer = "<div id='flightOptionsContent' class='Content is-scrollable'></div>";

    if(flightId != null){
        self.flightOptionsWorkspace.append(viewOptionsDataContainer);
        self.flightOptionsContent = $("div#flightOptionsContent");

        var pV = {
                action: self.actions["getParamListGivenQuantity"],
                data: {
                    flightId: flightId
                }
        };

        $.ajax({
            type: "POST",
            data: pV,
            dataType: 'json',
            url: FLIGHTS_VIEW_OPTIONS_SRC,
            async: true
        }).fail(function(msg){
            console.log(msg);
        }).done(function(answ) {
            if($("#paramsListLeftMenuRow").hasClass("LeftMenuRowSelected")){
                if(answ["status"] == "ok") {
                    var data = answ["data"],
                        flightOptionsContent =
                            document.getElementById(self.flightOptionsContent.attr('id')),
                        flightOptionsContent$ = $("#" + self.flightOptionsContent.attr('id'));
                    if(data['pagination']) {

                        $("#leftMenuOptionsView .SearchBox").prop('disabled', false).val("");
                        self.SupportSearch();

                        var pageNum = data['pageNum'],
                            totalPages = data['totalPages'];

                         $("<div></div>")
                            .attr('id', "paginationContainer")
                            .appendTo(flightOptionsContent$);

                         paginationContainer$ = $("#paginationContainer");

                         $("<div></div>")
                            .addClass('SelectedParams')
                            .attr('id', "selectedParams")
                            .appendTo(paginationContainer$);

                         $("<div></div>")
                            .addClass('SearchResult')
                            .attr('id', "searchResult")
                            .appendTo(paginationContainer$);

                         $("<div></div>")
                            .addClass('ParamsPagination')
                            .attr('id', "prevPage")
                            .attr("disabled", "disabled")
                            .text(self.langStr.viewOptionsPrevPage)
                            .appendTo(paginationContainer$);

                        $("<div></div>")
                            .addClass('ParamsPagination')
                            .attr('id', "nextPage")
                            .text(self.langStr.viewOptionsNextPage)
                            .appendTo(paginationContainer$);

                        $("<div></div>")
                            .addClass('ParamsPaginationInline')
                            .attr('id', "firstPage")
                            .text(self.langStr.viewOptionsFirstPage)
                            .appendTo(paginationContainer$);

                        $("<div></div>")
                            .addClass('ParamsPaginationInline')
                            .attr('id', "paginationCurPageOfTotal")
                            .text((pageNum + 1) + " " + self.langStr.viewOptionsOf + " " + totalPages)
                            .appendTo(paginationContainer$);

                        $("<div></div>")
                            .addClass('ParamsPaginationInline')
                            .attr('id', "lastPage")
                            .text(self.langStr.viewOptionsLastPage)
                            .appendTo(paginationContainer$);

                        flightOptionsContent.innerHTML +=
                            "<div id='bruTypeParamsPaginatedList' class='BruTypeParamsPaginatedList is-scrollable'>" +
                            data['bruTypeParams'] +
                            "</div>";

                        $("#bruTypeParamsPaginatedList").height(
                                self.window.innerHeight() -
                                self.flightOptionsTopMenu.height() -
                                self.flightOptionsOptions.height() -
                                paginationContainer$.outerHeight() - 180
                        );

                        self.SupportParamsChecking();
                        self.SupportColorPicker();

                        $("#prevPage").button()
                        .on('click', function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var paginationCurPageOfTotal = $("#paginationCurPageOfTotal"),
                            curpage = parseInt(paginationCurPageOfTotal.data("curpage")) - 1;

                            if(curpage >= 0){
                                self.ShowParamListPaginated(curpage, totalPages);
                                paginationCurPageOfTotal.data("curpage", curpage);
                                paginationCurPageOfTotal.button("option", "label",
                                    (curpage+1) + " " + self.langStr.viewOptionsOf + " " + (totalPages)
                                );
                            }
                        });

                        $("#nextPage").button()
                        .on('click', function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var paginationCurPageOfTotal = $("#paginationCurPageOfTotal"),
                                curpage = parseInt(paginationCurPageOfTotal.data("curpage")) + 1;

                            if(curpage < totalPages){
                                self.ShowParamListPaginated(curpage, totalPages);
                                paginationCurPageOfTotal.data("curpage", curpage);
                                paginationCurPageOfTotal.button("option", "label",
                                    (curpage+1) + " " + self.langStr.viewOptionsOf + " " + (totalPages)
                                );
                            }
                        });

                        $("#firstPage").button()
                        .on('click', function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var paginationCurPageOfTotal = $("#paginationCurPageOfTotal"),
                                curpage = 0;
                            self.ShowParamListPaginated(curpage, totalPages);
                            paginationCurPageOfTotal.data("curpage", curpage);
                            paginationCurPageOfTotal.button("option", "label",
                                (curpage+1) + " " + self.langStr.viewOptionsOf + " " + (totalPages)
                            );
                        });

                        $("#paginationCurPageOfTotal")
                        .button()
                        .data("curpage", '0') //set cur page 0
                        .on('click', function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var paginationCurPageOfTotal = $("#paginationCurPageOfTotal"),
                                curpage = Math.round(totalPages/2);
                            self.ShowParamListPaginated(curpage, totalPages);
                            paginationCurPageOfTotal.data("curpage", curpage);
                            paginationCurPageOfTotal.button("option", "label",
                                (curpage+1) + " " + self.langStr.viewOptionsOf + " " + (totalPages)
                            );
                        })

                        $("#lastPage").button()
                        .on('click', function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            var paginationCurPageOfTotal = $("#paginationCurPageOfTotal"),
                                curpage = totalPages - 1;
                            self.ShowParamListPaginated(curpage, totalPages);
                            paginationCurPageOfTotal.data("curpage", curpage);
                            paginationCurPageOfTotal.button("option", "label",
                                (curpage+1) + " " + self.langStr.viewOptionsOf + " " + (totalPages)
                            );
                        });

                    } else {
                        flightOptionsContent.innerHTML = data['bruTypeParams'];

                        self.SupportColorPicker();
                    }
                    self.ResizeFlightViewOptionsContainer();
                } else {
                    console.log(answ["error"]);
                }
            }
        });
    }

    return false;
}

FlightViewOptions.prototype.ShowParamListPaginated = function(pageNum, totalPages) {
    var self = this,
        flightId = self.flightId;

    if(flightId != null){
        var pV = {
                action: self.actions["getParamListGivenQuantity"],
                data: {
                    flightId: flightId,
                    pageNum: pageNum
                }
        };

        $.ajax({
            type: "POST",
            data: pV,
            dataType: 'json',
            url: FLIGHTS_VIEW_OPTIONS_SRC,
            async: true
        }).fail(function(msg){
            console.log(msg);
        }).done(function(answ) {
            if(answ["status"] == "ok") {
                var data = answ["data"],
                    pageNum = data['pageNum'],
                    totalPages = data['totalPages'],
                    prevPage = $("#prevPage"),
                    nextPage = $("#nextPage");

                var paramsPaginated = $("#bruTypeParamsPaginatedList");
                paramsPaginated.empty();
                paramsPaginated.append(data['bruTypeParams']);

                self.ResizeFlightViewOptionsContainer();
                self.SupportParamsChecking();
                self.SupportColorPicker();
            } else {
                console.log(answ["error"]);
            }
        });
    }

    return false;
}

FlightViewOptions.prototype.SupportParamsChecking = function(){
    var self = this;
    $(".ParamsCheckboxGroupPaged").on("click", function(e){
        var alreadyIssetInputs = $(".ParamsCheckboxGroup"),
            alreadyIssetInputVals = [];

        $.each(alreadyIssetInputs, function(ii, item){
            alreadyIssetInputVals.push.call(alreadyIssetInputVals, $(item).val());
        });

        var thisCheck = $(this),
            checkedVal = thisCheck.val();

        if($.inArray(checkedVal, alreadyIssetInputVals) == -1){
            var selectedParamsList$ = $("#selectedParams");
            if(selectedParamsList$.css("visibility") == 'hidden'){
                selectedParamsList$.css("visibility", 'visible');
            }

            var selectedParam = $("<label></label>").html(thisCheck.closest('label').html());

            if (thisCheck.is(':checked')) {
                /*thisCheck.prop('checked', false);*/
                selectedParam.find('input')
                    .prop('checked', true)
                    .removeClass("ParamsCheckboxGroupPaged")
                     .addClass("ParamsCheckboxGroup");
                selectedParamsList$.append(selectedParam);
            }

            $("#bruTypeParamsPaginatedList").height(
                    self.flightOptionsContent.innerHeight() -
                     $("#paginationContainer").outerHeight() - 20

            );
        } else {
            if (thisCheck.is(':checked')) {
                thisCheck.prop('checked', false);
            }
        }
    });
}

FlightViewOptions.prototype.SupportColorPicker = function(){
    var self = this;

    $.colorpicker.regional['current'] = {
            ok:                self.langStr.colorpickerOk,
            cancel:            self.langStr.colorpickerCancel,
            none:            self.langStr.colorpickerNone,
            button:            self.langStr.colorpickerButton,
            title:            self.langStr.colorpickerTitle,
            transparent:    self.langStr.colorpickerTransparent
        };

    $('input.colorpicker-popup').on("click", function(e){
        var $this = $(this);
        if($this.data("colorpicker") == false) {
            $this.colorpicker({
                regional: 'current',
                ok: function(event, color) {
                    var pV = {
                        action: self.actions["changeParamColor"],
                        data: {
                            flightId : self.flightId,
                            paramCode : $this.data("paramcode"),
                            color: color.formatted
                        }
                    };

                    $this.css({
                        'background-color': '#' + color.formatted,
                        'color': '#' + color.formatted
                    });

                    $.ajax({
                        dataType : "json",
                        type: "POST",
                        url : FLIGHTS_VIEW_OPTIONS_SRC,
                        data : pV,
                    });
                }
            })

            $this.data("colorpicker", 'true');
            $this.colorpicker('open');
        }
    });
}


FlightViewOptions.prototype.SupportSearch = function(){
    var self = this,
        searchBox = $("#leftMenuOptionsView .SearchBox");

    searchBox.on("input", function(e){
        var $this = $(this);
        var delay = 2000; // 2 seconds delay after last input

        clearTimeout($this.data('timer'));
        $this.data('timer', setTimeout(function(){
            $this.removeData('timer');
            var searchText = searchBox.val().trim(),
                  searchResult = $("#searchResult");

            searchResult.empty();

            if(searchText != ''){
                ReceiveSearchResult(searchText, self.flightId)
                .done(function(answ){
                    if(answ["status"] == "ok") {
                        var data = answ["data"];
                        var foundCount = data['foundCount'];

                        if (foundCount > 0) {
                            searchResult.css('visibility', 'visible');
                            var searchRes = data['searchedParams'];
                            searchResult.append(searchRes);
                            SupportSearchedParamsChecking();
                        }
                    } else {
                        console.log(answ['error']);
                    }
                });
            } else {
                searchResult.empty();
                searchResult.css('visibility', 'hidden');
            }
        }, delay));

    });

    function ReceiveSearchResult(req, flightId){
        if(flightId != null){
            var pV = {
                    action: self.actions["getSearchedParams"],
                    data: {
                        flightId: flightId,
                        request: req
                    }
            };

            return $.ajax({
                url: FLIGHTS_VIEW_OPTIONS_SRC,
                type: "POST",
                dataType: "json",
                async: true,
                data: pV
            }).fail(function(e){
                console.log(e);
            });
        }
    };

    function SupportSearchedParamsChecking(){
        $(".ParamsCheckboxSearched").on("click", function(e){
            var selectedParamsList$ = $("#selectedParams");
            if(selectedParamsList$.css("visibility") == 'hidden'){
                selectedParamsList$.css("visibility", 'visible');
            }

            var thisCheck = $(this);
                thisCheck.removeClass("ParamsCheckboxSearched");
                thisCheck.addClass("ParamsCheckboxGroup");
                selectedParam = $("<label></label>").html(thisCheck.closest('label').html());
            if (thisCheck.is(':checked') ) {
                thisCheck.prop('checked', false);
                selectedParam.find('input').prop('checked', true);
                selectedParamsList$.append(selectedParam);
            }
        });
    }
}

import { I18n }  from 'react-redux-i18n';

function FlightViewOptions(store)
{
    this.flightId = null;
    this.task = null;

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
            self.flightOptionsFactoryContainer.append(data['workspace']);
            self.flightOptionsWorkspace = $('div#flightOptionsWorkspace');

            if(self.task == null){
                self.ShowFlightViewTemplates();
            } else if(self.task === 'getTemplates'){
                self.ShowFlightViewTemplates();
            } else if(self.task === 'getEventsList'){
                self.ShowFlightViewEvents();
            } else if(self.task === 'getParamList'){
                self.ShowFlightViewParamsList();
            }
        } else {
            console.log(answ["error"]);
        }
    });
}

FlightViewOptions.prototype.ShowFlightViewTemplates = function() {
    if(this.flightOptionsWorkspace.html() != ''){
        this.flightOptionsWorkspace.empty();
    }

    this.ShowTempltList();
}

FlightViewOptions.prototype.ShowFlightViewEvents = function() {
    if(this.flightOptionsWorkspace.html() != ''){
        this.flightOptionsWorkspace.empty();
    }

    this.ShowEventsList();
    this.SupportUserComment();
}

FlightViewOptions.prototype.ShowFlightViewParamsList = function() {
    if(this.flightOptionsWorkspace.html() != ''){
        this.flightOptionsWorkspace.empty();
    }

    this.ShowParamList();
}

///====================================================
//EVENTS
///====================================================
FlightViewOptions.prototype.ShowTopMenuEventsListButtons = function(){
    var self = this,
        flightId = self.flightId;
    if(self.flightOptionsTopMenu != null){
        if(self.flightOptionsTopMenu.html() != ''){
            self.flightOptionsTopMenu.empty();
        }

        self.flightOptionsTopMenu.append('<label id="down" class="Down">' +
                '<span style="position:absolute; margin-top:5px;">&nbsp;' +
                self.langStr.flightViewOptionsShow +
                '</span>' +
                '</label>');

        $("<div></div>")
            .addClass("Separator")
            .appendTo(self.flightOptionsTopMenu);

        $("#down").on("click", function(e){
            if(self.flightOptionsLeftMenu != null){
                //check out what left menu option selected
                var currentOption = self.flightOptionsLeftMenu.find(".LeftMenuRowSelected").attr('id');

                if((currentOption = "eventsLeftMenuRow") && (flightId != null)){
                    self.DefaultTplParamsReceive(flightId).done(function(answ){
                        if(answ["status"] == 'ok'){
                            var data = answ["data"],
                            ap = data['ap'],
                            bp = data['bp'],
                            tplName = "last",
                            refparam = $(".ExceptionTableRow.ExeptionsTableRowSelected").data("refparam");

                            var params = new Array();
                            if (refparam) {
                                params.push(refparam);
                            }
                            for(var i = 0; i < ap.length; i++){
                                params.push(ap[i]);
                            }
                            for(var i = 0; i < bp.length; i++){
                                params.push(bp[i]);
                            }
                            params = params.getUnique();

                            if (params.length > 0) {
                                self.TplCreate(flightId, tplName, params).done(function(answ){
                                    if(answ["status"] == "ok") {
                                        var apParams = answ['data']['ap'],
                                            bpParams = answ['data']['bp'],

                                            startCopyTime = self.rangeSlider.data("startcopytime"),
                                            stepLength = self.rangeSlider.data("steplength"),
                                            startFrame = self.rangeSlider.slider("values", 0) / stepLength,
                                            endFrame = self.rangeSlider.slider("values", 1) / stepLength;

                                        var data = [flightId, tplName,
                                                stepLength, startCopyTime,
                                                startFrame, endFrame,
                                                apParams, bpParams];

                                        $(document).trigger("showChart", data);

                                    } else {
                                        console.log(answ["error"]);
                                    }
                                });
                            } else {
                                self.TplParamsReceive(flightId, 'events').done(function(answ) {
                                    if(answ["status"] == "ok") {
                                        var apParams = answ['data']['ap'],
                                            bpParams = answ['data']['bp'],

                                            startCopyTime = self.rangeSlider.data("startcopytime"),
                                            stepLength = self.rangeSlider.data("steplength"),
                                            startFrame = self.rangeSlider.slider("values", 0) / stepLength,
                                            endFrame = self.rangeSlider.slider("values", 1) / stepLength;

                                        var data = [flightId, tplName,
                                                stepLength, startCopyTime,
                                                startFrame, endFrame,
                                                apParams, bpParams];

                                        $(document).trigger("showChart", data);

                                    } else {
                                        console.log(answ["error"]);
                                    }
                                });
                            }
                        } else {
                            console.log(answ["error"] );
                        }
                    });
                }
            }
        });

        /* ---------------- */

        var printEventBlank$ = $("<div></div>")
            .attr("id", "printEventBlank")
            .addClass("TopMenuLeftSecondButt")
            .appendTo(self.flightOptionsTopMenu);

        $("<span></span>")
            .css({
                "position": "absolute",
                "margin-top": "5px",
                "margin-left": "5px",
            })
            .text(self.langStr.eventListSave)
            .appendTo(printEventBlank$);

        printEventBlank$.on("click", function(e){
            var monochromeEventsPrint = $("#monochromeEventsPrint"),
                eventsPrintAction = 'printBlank';

            if((monochromeEventsPrint.length > 0) &&
                    (monochromeEventsPrint.prop("checked") == true)){
                eventsPrintAction = 'monochromePrintBlank';
            }

            var $accordionButtons = $(".exceptions-accordion-title[data-shown='true']");
            if($accordionButtons.length > 0) {
                var sections = [];
                $.each($accordionButtons, function(index, item) {
                    sections.push($accordionButtons.eq(index).data('section'));
                });

                sections.join(',');

                $('<form></form>', {
                    method: 'POST',
                    action: ENTRY_URL,
                    target: '_blank'
                  })
                  .css('display', 'none')
                  .append($('<input/>', {
                    name: 'data[flightId]',
                    value: flightId
                  }))
                  .append($('<input/>', {
                    name: 'data[sections]',
                    value: sections
                  }))
                  .append($('<input/>', {
                    name: 'action',
                    value: 'printer/'+eventsPrintAction
                  })).appendTo(
                      $('body')
                  ).submit();
            }

            return false;
        });
    }
}

FlightViewOptions.prototype.ShowFlightViewEventsListOptions = function() {
    //MainContainerUploaderOptions
    var self = this,
    flightId = self.flightId;

    if(flightId != null){
        var viewOptionsStr = "<div id='flightOptionsOptions' class='OptionsMenu'>" +
            "<table v-align='top' style='width:800px;'><tr>" +
                    "<td>" +
                        "<label><nobr>" + self.langStr.flightViewOptionsTimeRange + " - " + "</nobr></label>" +
                    "</td>" +
                    "<td class='SliderRangeTD'>" +
                        "<div id='sliderRange'></div>" + //font size for slider
                    "</td>" +
                    "<td class='OptionsMenuTableTD'>" +
                        "<input type='text' id='amount' readonly style='border:0; font-size:24px; color: #585858;'>" +
                    "</td>" +
                    "<td  class='OptionsMenuTableTD'>" +
                        "<input type='checkbox' id='monochromeEventsPrint'>" +
                    "</td>" +
                    "<td class='OptionsMenuTableTD'>" +
                        "<label class='OptionsTwoLineLabel'><nobr>" +
                            self.langStr.monochromeEventsPrint +
                        "</label></nobr>" +
                    "</td>" +
            "</td></tr></table></div>";

        self.flightOptionsWorkspace.append(viewOptionsStr);
        self.flightOptionsOptions = $("div#flightOptionsOptions");


        var pV = {
                action: "viewOptions/getFlightDuration",
                data: {
                    flightId: flightId
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

FlightViewOptions.prototype.ShowEventsList = function() {
    var self = this,
        flightId = self.flightId,
        viewOptionsDataContainer = "<div id='flightOptionsContent' class='Content'></div>";

    if(flightId != null){
        self.flightOptionsWorkspace.append(viewOptionsDataContainer);
        self.flightOptionsContent = $("div#flightOptionsContent");

        var pV = {
            action: "viewOptions/getEventsList",
            data: {
                flightId: flightId
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
            if (answ["status"] == "ok") {
                var data = answ["data"],
                flightOptionsContent =
                    document.getElementById(self.flightOptionsContent.attr('id'));
                flightOptionsContent.innerHTML = data['eventsListHeader']
                    + '<div class="container__events-list">' + data['eventsList'] + '</div>';

                var $accordionButtons = $(".exceptions-accordion-title");
                self.SupportAccordion($accordionButtons);

                var exceptionTableRow = $(".ExceptionTableRow");
                self.SupportReliabilityUncheck.call(self, exceptionTableRow, flightId);

                $('.container__events-list').height(
                    $('#flightOptionsContent').height()
                    - $('.container__events-header').eq(0).outerHeight(true)
                );

                exceptionTableRow.on("click", function(e){
                    var row = $(this);

                    $.each(exceptionTableRow, function(index, item){
                        $(item).removeClass("ExeptionsTableRowSelected");
                    });

                    row.addClass("ExeptionsTableRowSelected");

                    var rowStartframe = row.data("startframe"),
                    rowEndframe = row.data("endframe"),
                    steplength = self.rangeSlider.data("steplength"),
                    from = rowStartframe * steplength * 0.5,
                    to = rowEndframe * steplength * 1.5;

                    self.rangeSlider.slider('option', { values: [from, to] });
                });

                $('#comments__btn').on("click", function(e) {
                    $.post(
                        ENTRY_URL,
                        {
                            action: 'viewOptions/saveFlightComment',
                            data: $('#events-header__comments').serialize()
                        },
                        function(answ) {
                            $('#comments__btn').addClass('is-analyzed');
                            location.reload(true);
                        }
                    )
                });
            } else {
                console.log(answ["error"]);
            }
        });
    }

    return false;
};

FlightViewOptions.prototype.SupportReliabilityUncheck = function(exceptionTableRow, flightId) {
    var self = this;

    exceptionTableRow.find(".reliability").on('click', function(e){
        var this$ = $(this),
            excId = this$.data('excid'),
            state = this$.prop('checked');

        var pV = {
                action : "viewOptions/setEventReliability",
                data: {
                    flightId: flightId,
                    excId: excId,
                    state : state
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
        });
    });
};

FlightViewOptions.prototype.SupportAccordion = function($accordionButtons) {
    var self = this;
    $accordionButtons.click(function(event) {
        var target = $(event.currentTarget);
        var dataShown = (target.attr('data-shown') === 'false') ? 'true' : 'false';
        target.attr('data-shown', dataShown).next().slideToggle();
    });
};


FlightViewOptions.prototype.SupportUserComment = function() {
    var that = this;

    function removeTextarea(event) {
        if((event.target
                && !$(event.target).hasClass('events_user-comment')
                && !$(event.target).hasClass('events_user-comment-texarea')
            )
            || (event.which == 13)
        ) {
            $.each($('.events_user-comment-texarea'), function(key, value) {
                var $el = $(value);
                var text = $el.val();
                var excId = $el.parents('.events_user-comment').first().data('excid');

                $.post(ENTRY_URL,
                    {
                        action: 'viewOptions/updateComment',
                        data: {
                            flightId: that.flightId,
                            excId: excId,
                            text: text
                        }
                    },
                    function() {
                        $el.parent().text(text);
                    }
                );
            });
            $(document).off('click', removeTextarea);
            $(document).off('keypress', removeTextarea);
        }
    };

    $('#flightOptionsContent').on('click', function(event) {
        var $el = $(event.target);

        if($el.hasClass('events_user-comment')
            && ($el.attr('disabled') !== 'disabled')
            && ($el.find('textarea').length === 0)
        ) {
            var text = $el.text();
            var $textarea = $('<textarea></textarea>');
            $textarea.addClass('events_user-comment-texarea');
            $el.append($textarea);
            $textarea.focus();

            $(document).click(removeTextarea);
            $(document).keypress(removeTextarea);
        }
    });
}

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
                '<span style="position:absolute; margin-top:5px;">&nbsp;' +
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

                            $(document).trigger("showChart", data);

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
                        "<img id='saveTplProgress' class='SmallProgressImg' src='/front/stylesheets/basicImg/loading.gif'/>" +
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
                action: "viewOptions/getFlightDuration",
                data: {
                    flightId: flightId
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
        viewOptionsDataContainer = "<div id='flightOptionsContent' class='Content'></div>";

    if(flightId != null){
        self.flightOptionsWorkspace.append(viewOptionsDataContainer);
        self.flightOptionsContent = $("div#flightOptionsContent");

        $.ajax({
            type: "POST",
            data: {
                action: "viewOptions/getParamListGivenQuantity",
                data: {
                    flightId: flightId
                }
            },
            dataType: 'json',
            url: ENTRY_URL,
            async: true
        }).fail(function(msg){
            console.log(msg);
        }).done(function(answ) {
            if (answ["status"] === "ok") {
                var data = answ["data"];
                let flightOptionsContent =
                        document.getElementById(self.flightOptionsContent.attr('id')),
                    flightOptionsContent$ = $("#" + self.flightOptionsContent.attr('id'));
                if (data['pagination']) {

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
                        "<div id='bruTypeParamsPaginatedList' class='BruTypeParamsPaginatedList'>" +
                        data['bruTypeParams'] +
                        "</div>";

                    $("#bruTypeParamsPaginatedList").height(
                            $(window).innerHeight() -
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
            } else {
                console.log(answ["error"]);
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
                action: "viewOptions/getParamListGivenQuantity",
                data: {
                    flightId: flightId,
                    pageNum: pageNum
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
                var data = answ["data"],
                    pageNum = data['pageNum'],
                    totalPages = data['totalPages'],
                    prevPage = $("#prevPage"),
                    nextPage = $("#nextPage");

                var paramsPaginated = $("#bruTypeParamsPaginatedList");
                paramsPaginated.empty();
                paramsPaginated.append(data['bruTypeParams']);

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
        ok: I18n.t('colorpicker.ok'),
        cancel: I18n.t('colorpicker.cancel'),
        none: I18n.t('colorpicker.none'),
        button: I18n.t('colorpicker.button'),
        title: I18n.t('colorpicker.title'),
        transparent: I18n.t('colorpicker.transparent')
    };

    $('input.colorpicker-popup').on("click", function(e){
        var $this = $(this);
        if($this.data("colorpicker") == false) {
            $this.colorpicker({
                regional: 'current',
                ok: function(event, color) {
                    var pV = {
                        action: "viewOptions/changeParamColor",
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
                        url : ENTRY_URL,
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
                    action: "viewOptions/getSearchedParams",
                    data: {
                        flightId: flightId,
                        request: req
                    }
            };

            return $.ajax({
                url: ENTRY_URL,
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

///====================================================
//TEMPLATES
///====================================================
FlightViewOptions.prototype.ShowTopMenuTempltListButtons = function(){
    var self = this;
    if(self.flightOptionsTopMenu != null){
        if(self.flightOptionsTopMenu.html() != ''){
            self.flightOptionsTopMenu.empty();
        }

        self.flightOptionsTopMenu.append('<label id="down" class="Down">' +
                '<span style="position:absolute; margin-top:5px;">&nbsp;' +
                self.langStr.flightViewOptionsShow +
                '</span>' +
                '</label>');

        self.flightOptionsTopMenu.append('<label id="here" class="HereRight">' +
                '<span style="position:absolute; margin-top:8px;">&nbsp;' +
                self.langStr.flightViewOptionsEditTpls +
                '</span>' +
                '</label>');

        $("#down").on("click", function(e){
            if(self.flightOptionsLeftMenu != null){
                //check out what left menu option selected
                var currentOption = self.flightOptionsLeftMenu .find(".LeftMenuRowSelected").attr('id');

                //if templatesLeftMenuRow check one template selected or more
                //if one just send it
                //if few create uptade 'last' tpl and pass with it forward
                if(currentOption = "templatesLeftMenuRow"){
                    var tplName = 'last',
                    selectedOptions = $("#tplList option:selected");

                    if(selectedOptions.length > 1)
                    {
                        var params = new Array();
                        $.each(selectedOptions, function(index, item){
                            var item = $(item);
                            params = params.concat(item.data("params").split(", ")); // Merges both arrays
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

                                $(document).trigger("showChart", data);

                            } else {
                                console.log(answ["error"]);
                            }
                     });

                    } else if(selectedOptions.length == 1){

                        var tplName = selectedOptions.attr("name"),
                            TplParamsReceiver = self.TplParamsReceive(self.flightId, tplName);

                        TplParamsReceiver.done(function(answ) {
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

                                $(document).trigger("showChart", data);

                            } else {
                                console.log(answ["error"]);
                            }
                        });
                    }
                }
            }
        });

        $("#here").on("click", function(e){
            var pV = {
                    action: "flights/getFlightFdrId",
                    data: {
                        flightId: self.flightId
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
            }).done(function(answ){
                if(answ['status'] == 'ok'){

                    var bruTypeId = answ["data"]['bruTypeId'],
                        data = [bruTypeId,
                                'editingBruTypeTemplates',
                                self.flightOptionsFactoryContainer];

                    $(document).trigger("showBruTypeEditingForm", data);
                } else {
                    console.log(answ['error']);
                }
            });
        });
    }
}

FlightViewOptions.prototype.ShowFlightViewTempltListOptions = function() {
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
                        "<input type='text' id='amount' readonly style='border:0; font-size:24px; color: #585858;'>" +
                    "</td>" +
            "</td></tr></table></div>";

        self.flightOptionsWorkspace.append(viewOptionsStr);
        self.flightOptionsOptions = $("div#flightOptionsOptions");

        var pV = {
                action: "viewOptions/getFlightDuration",
                data: {
                    flightId: flightId
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

String.prototype.toHHMMSS = function () {
    var sec_num = parseInt(this, 10); // don't forget the second parm
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0" + hours;}
    if (minutes < 10) {minutes = "0" + minutes;}
    if (seconds < 10) {seconds = "0" + seconds;}
    var time    = hours+':'+minutes+':'+seconds;
    return time;
};

Array.prototype.getUnique = function(){
   var u = {}, a = [];
   for(var i = 0, l = this.length; i < l; ++i){
      if(u.hasOwnProperty(this[i])) {
         continue;
      }
      a.push(this[i]);
      u[this[i]] = 1;
   }
   return a;
}

FlightViewOptions.prototype.TplCreate = function(flightId, tplName, params) {
    var self = this;

    var pV = {
            action: "viewOptions/createTpl",
            data: {
                flightId: flightId,
                tplName: tplName,
                params: params
            }
    };

    return $.ajax({
        type: "POST",
        data: pV,
        dataType: 'json',
        url: ENTRY_URL,
        async: true
    }).fail(function(msg){
        console.log(msg);
    });
}

FlightViewOptions.prototype.TplParamsReceive = function(flightId, tplName) {
    var self = this;

    var pV = {
            action: "viewOptions/getParamCodesByTemplate",
            data: {
                flightId: flightId,
                tplName: tplName
            }
    };

    return $.ajax({
        type: "POST",
        data: pV,
        dataType: 'json',
        url: ENTRY_URL,
        async: true
    }).fail(function(msg){
        console.log(msg);
    });
}

FlightViewOptions.prototype.DefaultTplParamsReceive = function(flightId) {
    var self = this;

    var pV = {
            action: "viewOptions/getDefaultTemplateParamCodes",
            data: {
                flightId: flightId
            }
    };

    return $.ajax({
        type: "POST",
        data: pV,
        dataType: 'json',
        url: ENTRY_URL,
        async: true
    }).fail(function(msg){
        console.log(msg);
    });
}

FlightViewOptions.prototype.ShowTempltList = function() {
    var self = this,
        flightId = self.flightId,
        viewOptionsDataContainer = "<div id='flightOptionsContent' class='Content'></div>";

    if(flightId != null){
        self.flightOptionsWorkspace.append(viewOptionsDataContainer);
        self.flightOptionsContent = $("div#flightOptionsContent");

        $.ajax({
            type: "POST",
            data: {
                action: "viewOptions/getBruTemplates",
                data: {
                    flightId: flightId
                }
            },
            dataType: 'json',
            url: ENTRY_URL,
            async: true
        }).fail(function(msg){
            console.log(msg);
        }).done(function(answ) {
            if (answ["status"] === "ok") {
                var data = answ["data"];
                var flightOptionsContent =
                    document.getElementById(self.flightOptionsContent.attr('id'));
                flightOptionsContent.innerHTML = data['bruTypeTpls'];

                var tplComment = $("#tplComment");

                tplComment.append(" ");
                $.each($("#tplList option:selected"), function(index, item){
                    var text = $(item).data('comment').replace(/\r?\n/g, '&#13;');
                    tplComment.append(text);
                });

                $("#tplList").on("click", function(e){
                    tplComment.empty();
                    tplComment.append(" ");
                    $.each($("#tplList option:selected"), function(index, item){
                        var text = $(item).data('comment').replace(/\r?\n/g, '&#13;');
                        tplComment.append(text);
                    });
                });

            } else {
                console.log(answ["error"]);
            }
        });
    }

    return false;
}

module.exports = FlightViewOptions;

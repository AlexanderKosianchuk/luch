
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
                '<span style="position:absolute; margin-top:8px;">&nbsp;' +
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
                            params.push(refparam);
                            for(var i = 0; i < ap.length; i++){
                                params.push(ap[i]);
                            }
                            for(var i = 0; i < bp.length; i++){
                                params.push(bp[i]);
                            }
                            params = params.getUnique();

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

                                    self.eventHandler.trigger("showChart", data);

                                } else {
                                    console.log(answ["error"]);
                                }
                            });

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
                "margin-top": "8px",
                "margin-left": "5px",
            })
            .text(self.langStr.eventListSave)
            .appendTo(printEventBlank$);

        printEventBlank$.on("click", function(e){
            var monochromeEventsPrint = $("#monochromeEventsPrint"),
                eventsPrintAction = self.printerTasks['printBlank'];

            if((monochromeEventsPrint.length > 0) &&
                    (monochromeEventsPrint.prop("checked") == true)){
                eventsPrintAction = self.printerTasks['monochromePrintBlank'];
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
                    action: '/view/eventsBlank.php',
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
                    value: eventsPrintAction
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

FlightViewOptions.prototype.ShowEventsList = function() {
    var self = this,
        flightId = self.flightId,
        viewOptionsDataContainer = "<div id='flightOptionsContent' class='Content'></div>";

    if(flightId != null){
        self.flightOptionsWorkspace.append(viewOptionsDataContainer);
        self.flightOptionsContent = $("div#flightOptionsContent");

        var pV = {
                action: self.actions["getEventsList"],
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
            if($("#eventsLeftMenuRow").hasClass("LeftMenuRowSelected")){
                if(answ["status"] == "ok") {
                    var data = answ["data"],
                    flightOptionsContent =
                        document.getElementById(self.flightOptionsContent.attr('id'));
                    flightOptionsContent.innerHTML = data['eventsList'];

                    self.ResizeFlightViewOptionsContainer();

                    var $accordionButtons = $(".exceptions-accordion-title");
                    self.SupportAccordion($accordionButtons);

                    var exceptionTableRow = $(".ExceptionTableRow");
                    self.SupportReliabilityUncheck.call(self, exceptionTableRow, flightId);
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
                } else {
                    console.log(answ["error"]);
                }
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
                action : self.actions["setEventReliability"],
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
            url: FLIGHTS_VIEW_OPTIONS_SRC,
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

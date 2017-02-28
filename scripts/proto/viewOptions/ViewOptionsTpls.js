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
                '<span style="position:absolute; margin-top:8px;">&nbsp;' +
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

                                self.eventHandler.trigger("showChart", data);

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

                                self.eventHandler.trigger("showChart", data);

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

                    self.eventHandler.trigger("showBruTypeEditingForm", data);
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
        viewOptionsDataContainer = "<div id='flightOptionsContent' class='Content is-scrollable'></div>";

    if(flightId != null){
        self.flightOptionsWorkspace.append(viewOptionsDataContainer);
        self.flightOptionsContent = $("div#flightOptionsContent");

        var pV = {
                action: "viewOptions/getBruTemplates",
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

            if($("#templatesLeftMenuRow").hasClass("LeftMenuRowSelected")){
                if(answ["status"] == "ok") {
                    var data = answ["data"],
                    flightOptionsContent =
                        document.getElementById(self.flightOptionsContent.attr('id'));
                    flightOptionsContent.innerHTML = data['bruTypeTpls'];

                    self.ResizeFlightViewOptionsContainer();

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
            }
        });
    }

    return false;
}

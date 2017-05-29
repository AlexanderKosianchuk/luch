import 'stylesheets/pages/viewOptionsParams.css';
import 'stylesheets/pages/viewOptionsEvents.css';

import { I18n }  from 'react-redux-i18n';
import changeFlightParamCheckstateAction from 'actions/changeFlightParamCheckstate';

function FlightViewOptions(store)
{
    this.flightId = null;
    this.task = null;
    this.store = store;

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

            if (self.task == null){
                self.ShowFlightViewEvents();
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

                    flightOptionsContent.innerHTML = data['bruTypeParams'];

                    $('.flight-view-oprions-param').click(function(event) {
                        let item = $(this);

                        self.store.dispatch(changeFlightParamCheckstateAction({
                            id: item.data('id'),
                            paramType: item.data('type'),
                            state: item.prop('checked')
                        }));
                    });

                    self.SupportColorPicker();
            } else {
                console.log(answ["error"]);
            }
        });
    }

    return false;
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

export default FlightViewOptions;

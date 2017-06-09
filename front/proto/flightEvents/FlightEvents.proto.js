import 'stylesheets/pages/viewOptionsEvents.css';

import { I18n }  from 'react-redux-i18n';
import toggleEventsSection from 'actions/toggleEventsSection';

function FlightEvents(store)
{
    this.flightId = null;
    this.task = null;
    this.store = store;

    this.flightOptionsFactoryContainer = null;
    this.flightOptionsWorkspace = null;
    this.flightOptionsContent = null;
}

FlightEvents.prototype.FillFactoryContaider = function(factoryContainer) {
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

            self.ShowFlightViewEvents();
        } else {
            console.log(answ["error"]);
        }
    });
}

FlightEvents.prototype.ShowFlightViewEvents = function() {
    if(this.flightOptionsWorkspace.html() != ''){
        this.flightOptionsWorkspace.empty();
    }

    this.ShowEventsList();
    this.SupportUserComment();
}

///====================================================
//EVENTS
///====================================================

FlightEvents.prototype.ShowEventsList = function() {
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

                exceptionTableRow.on("click", function(e){
                    var row = $(this);

                    $.each(exceptionTableRow, function(index, item){
                        $(item).removeClass("ExeptionsTableRowSelected");
                    });

                    row.addClass("ExeptionsTableRowSelected");
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

FlightEvents.prototype.SupportReliabilityUncheck = function(exceptionTableRow, flightId) {
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

FlightEvents.prototype.SupportAccordion = function($accordionButtons) {
    var self = this;
    $accordionButtons.click(function(event) {
        var target = $(event.currentTarget);
        var dataShown = (target.attr('data-shown') === 'false') ? 'true' : 'false';
        target.attr('data-shown', dataShown).next().slideToggle();

        self.store.dispatch(toggleEventsSection({
            section: target.data('section'),
            isShown: (dataShown === 'false') ? false : true
        }));
    });

    $.each($accordionButtons, (index, item) => {
        self.store.dispatch(toggleEventsSection({
            section: $(item).data('section'),
            isShown: ($(item).data('shown') === 'false') ? false : true
        }));
    });
};


FlightEvents.prototype.SupportUserComment = function() {
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

export default FlightEvents;

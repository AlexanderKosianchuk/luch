import 'stylesheets/pages/flight.css';

import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { I18n } from 'react-redux-i18n';

import redirectAction from 'actions/redirect';

function FlightList(store) {
    this.store = store;

    this.view = 'tree';
    this.flightListFactoryContainer = null;
    this.flightListWorkspace = null;
    this.flightListOptions = null;
    this.flightListContent = null;
}

FlightList.prototype.setView = function(view) {
    this.view = view;
}

FlightList.prototype.FillFactoryContaider = function(factoryContainer) {
    var self = this;
    self.flightListFactoryContainer = factoryContainer;

    self.flightListFactoryContainer.empty();

    self.flightListFactoryContainer.append("<div id='flightListWorkspace' class='WorkSpace'></div>");
    self.flightListWorkspace = $("div#flightListWorkspace");

    self.flightListWorkspace.on("dblclick", ".JstreeContentItemFlight", function(event) {
        let currentTarget = event.currentTarget;
        let flightId = $(currentTarget).find("[data-flightid]").data("flightid");
        self.store.dispatch(redirectAction('/flight-events/' + flightId));
        return false;
    });

    self.flightListWorkspace.append("<div id='flightListContent' class='Content'></div>");
    self.flightListContent = $("div#flightListContent");

    if (self.view === 'table') {
        self.ShowFlightsTable();
    }

    self.bindMenuEvents();
}

FlightList.prototype.bindMenuEvents = function() {
    let flightMenuService = [
        ['flightMenuService:selectAll', this.selectAll.bind(this)],
        ['flightMenuService:exportCoordinates', this.exportCoordinates.bind(this)],
        ['flightMenuService:exportItem', this.export.bind(this)],
        ['flightMenuService:processItem', this.process.bind(this)],
        ['flightMenuService:deleteItem', this.delete.bind(this)],
        ['flightMenuService:removeSelection', this.removeSelection.bind(this)],
        ['flightMenuService:rename', this.rename.bind(this)]
    ];

    flightMenuService.forEach((item, index) => {
        $(document).off(item[0]);
        $(document).on(item[0], item[1]);
    });
}

FlightList.prototype.DeleteItem = function(type, id) {
    var self = this;

    var pV = {
        action: "flights/itemDelete",
        data: {
            type: type,
            id: id
        }
    };

    return $.ajax({
        type: "POST",
        data: pV,
        url: ENTRY_URL,
        dataType: 'json',
        async: true
    }).fail(function(msg){
        console.log(msg);
    });
}

FlightList.prototype.ProcessItem = function(id) {
    var self = this;

    var pV = {
        action: "flights/processFlight",
        data: {
            id: id
        }
    };

    return $.ajax({
        type: "POST",
        data: pV,
        url: ENTRY_URL,
        dataType: 'json',
        async: true
    }).fail(function(msg){
        console.log(msg);
    });
}

FlightList.prototype.ExportItem = function(flightIds, folderDest) {
    var self = this;

    var pV = {
        action: "flights/itemExport",
        data: {
            flightIds: flightIds,
            folderDest: folderDest
        }
    };

    return $.ajax({
        type: "POST",
        data: pV,
        url: ENTRY_URL,
        dataType: 'json',
        async: true
    }).done(function(msg){
        if(msg['status'] === 'ok') {
            window.location = msg['zipUrl'];
        }
    }).fail(function(msg){
        console.log(msg);
    });
}

FlightList.prototype.getFlightListSelectedItems = function() {
    var checked = $(".ItemsCheck:checked"),
        folders = new Array(),
        flights = new Array();

    $.each(checked, function(i, el){
        var el = $(el);
        if(el.data('type') == 'flight'){
            flights.push(el.data('flightid'));
        } else if(el.data('type') == 'folder') {
            folders.push(el.data('folderdestination'));
        }
    });

    return {
        selectedFlights: flights,
        selectedFolders: folders
    }
}

/* ==================================================
 * TABLE VIEW
 * ================================================== */

FlightList.prototype.ShowFlightsTable = function() {
    var self = this;

    self.flightListContent.slideUp(function(e){
        self.flightListContent.empty();
                var pV = {
                    action: "flights/flightListTable",
                    data: {
                        data: 'data'
                    }
                };

                $.ajax({
                    type: "POST",
                    data: pV,
                    url: ENTRY_URL,
                    dataType: 'json',
                    async: true,
                    success: function(answ) {
                        if(answ['status'] == 'ok'){
                            var flightList = answ['data'],
                                sortCol = answ['sortCol'],
                                sortType = answ['sortType'];
                            self.flightListContent.append(flightList);
                            self.flightListContent.slideDown(function(e){
                                self.SupportDataTable(sortCol, sortType);
                            });
                        } else {
                            console.log(data['error']);
                        }
                    }
                }).fail(function(msg){
                    console.log(msg);
                });
            /*});
        });*/
    });
};

FlightList.prototype.SupportDataTable = function(sortColumn, sortType) {
    var self = this,
        sortType = sortType.toLowerCase();

    var oTable = $('#flightTable').dataTable( {
        "bInfo": false,
        "bSort": true,
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [0] },
            { "sClass": "FlightTableCheckboxCenter", 'aTargets': [0] }
        ],
        "order": [[ sortColumn, sortType ]],
        "bFilter": false,
        "bLengthChange": false,
        "bAutoWidth": false,
        "bProcessing": true,
        "bServerSide": true,
        "aLengthMenu": false,
        "bPaginate": false,
        "sAjaxSource": ENTRY_URL,
        "fnServerData": function ( sSource, aoData, fnCallback) {
            var pV = {
                action: "flights/segmentTable",
                data: {
                    data: aoData
                }
            };

            $.ajax({
                "dataType": 'json',
                "type": "POST",
                "url": sSource,
                "data": pV,
                "success": fnCallback
            }).done(function(a){
                let selectedItems = self.getFlightListSelectedItems();

                $(".ItemsCheck").on("change", function(e){
                    let selectedItems = self.getFlightListSelectedItems();
                });
            }).fail(function(a){
                console.log(a);
            });
        },
        "oLanguage": {
            sLengthMenu: I18n.t('dataTables.sLengthMenu'),
            sZeroRecords: I18n.t('dataTables.sZeroRecords'),
            sInfo: I18n.t('dataTables.sInfo'),
            sInfoEmpty: I18n.t('dataTables.sInfoEmpty'),
            sInfoFiltered: I18n.t('dataTables.sInfoFiltered'),
            sSearch: I18n.t('dataTables.sSearch'),
            sProcessing: I18n.t('dataTables.sProcessing'),
            oPaginate: {
                sFirst: I18n.t('dataTables.oPaginate.sFirst'),
                sNext: I18n.t('dataTables.oPaginate.sNext'),
                sPrevious: I18n.t('dataTables.oPaginate.sPrevious'),
                sLast: I18n.t('dataTables.oPaginate.sLast'),
            }
        },
    });

    $("#tableCheckAllItems").on("click", function(e){
        var el = $(e.target);

        if(el.attr("checked") == "checked"){
            $(".ItemsCheck").removeAttr("checked");
            $(".ItemsCheck").prop("checked", false);
            el.removeAttr("checked");
        } else {
            $(".ItemsCheck").attr("checked", "checked");
            $(".ItemsCheck").prop("checked", true);
            el.attr("checked", "checked");
        }
    });
}

FlightList.prototype.selectAll = function(e){
    $.each($(".ItemsCheck"), function(i, el){
        var el = $(el).prop('checked', true);
    });
    let selectedItems = this.getFlightListSelectedItems();
};

FlightList.prototype.rename = function(e){
    var inputItemsCheck = $(".ItemsCheck:checked"),
    id = inputItemsCheck.data("folderdestination"),
    parent = inputItemsCheck.parent(),
    row = parent.parent(),
    parentText = parent.text();
    parent.text("");

    parent.append(inputItemsCheck);
    parent.append("<input id='currentChangedNameFolder' size='50' value='"+parentText+"'/>");

    row.off("click");
    row.on("click", function(e){
        var nodeName = $(e.target)[0].tagName;
        if(nodeName == "DIV"){
            var currentChangedNameFolder = $("#currentChangedNameFolder").val();
            parent.text("");
            parent.append(inputItemsCheck);
            parent.append(currentChangedNameFolder);

            self.RenameFolder(id, currentChangedNameFolder).done(function(answ) {
                if(answ['status'] != 'ok'){
                    console.log(answ['data']['error']);
                }
            });
        }
    });
};

FlightList.prototype.removeSelection = function(e){
    $.each($("input.ItemsCheck:checked"), function(i, el){
        var el = $(el).prop('checked', false);
    });
    let selectedItems = this.getFlightListSelectedItems();
};

FlightList.prototype.delete = function(e){
    var inputItemsCheck = $("input.ItemsCheck:checked");
    var deletedCount = 0;
    var self = this;

    $.each(inputItemsCheck, function(i, el){
        var el = $(el),
            type = el.data('type'),
            id = undefined;

        if(type == 'folder'){
            id = el.data('folderdestination');
        } else if(type == 'flight'){
            id = el.data('flightid');
        }
        self.DeleteItem(type, id).done(function(answ) {
            if(answ['status'] == 'ok'){
                el.removeAttr("checked");
                var parent = el.parents("tr") || el.parents(".JstreeContentItemFlight");
                parent.fadeOut(200).remove();
                deletedCount++;

                if (inputItemsCheck.length === deletedCount) {
                    self.ShowFlightsTree();
                }
            } else {
                console.log(answ['data']['error']);
            }
        });
    });
};

FlightList.prototype.process = function(e){
    var inputItemsCheck = $("input.ItemsCheck:checked");
    var self = this;

    $.each(inputItemsCheck, function(i, el){
        var el = $(el),
            type = el.data('type'),
            id = undefined;

        if(type == 'flight'){
            id = el.data('flightid');
            self.ProcessItem(id).done(function(answ) {
                if(answ['status'] == 'ok'){
                    el.removeAttr("checked");
                    var parent = el.parents("li");
                    parent.fadeOut(200);
                } else {
                    console.log(answ['data']['error']);
                }
            });
        }
    });
};

FlightList.prototype.export = function(e){
    var inputItemsCheck = $("input.ItemsCheck:checked");
    var self = this;

    $.each(inputItemsCheck, function(i, el){
        var el = $(el),
            type = el.data('type'),
            id = undefined;

        if(type == 'flight'){
            id = el.data('flightid');
            self.ExportItem(id).done(function(answ) {
                if(answ['status'] == 'ok'){
                    el.removeAttr("checked");
                    var parent = el.parents("li");
                    parent.fadeOut(200);
                } else {
                    console.log(answ['data']['error']);
                }
            });
        }
    });
};

FlightList.prototype.exportCoordinates = function(event) {
    var text = $("li#exportCoordinates").text();
    var inputItemsCheck = $("input.ItemsCheck:checked");

    if (inputItemsCheck.length) {
        let flightid = inputItemsCheck.data('flightid');
        let form = $('#export-coordinates');

        if (form.length) {
            form.remove()
        }

        form = $('<form></form>', {
            id: 'export-coordinates',
            action: ENTRY_URL,
            target: '_blank'
        }).css('display', 'none');

        form.append(
            $('<input>', {
                name: 'action',
                value: 'flights/coordinates'
            })
        )

        form.append(
            $('<input>', {
                name: 'id',
                value: flightid
            })
        )

        $('body').append(form);

        form.submit();
    }
};

export default FlightList;

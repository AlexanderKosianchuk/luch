/*jslint browser: true*/
/*global $, jQuery*/

var CALIBRATION_SRC = location.protocol + '//' + location.host + "/controller/CalibrationController.php";

function Calibration($window, document, langStr, eventHandler) {
    'use strict';

    var userId = null,
        calibrationWorkspace,
        calibrationOptions = null,
        calibrationFormContent = null;

    this.userId = null;
    this.task = null;

    this.resizeContainer = function(e)
    {
        eventHandler.trigger("resizeShowcase");
        return this;
    };

    this.FillFactoryContaider = function(calibrationWorkspace)
    {
        var that = this;
        this.calibrationWorkspace = calibrationWorkspace;
        this.calibrationWorkspace.empty();
        var dfd = this.calibrationFormOptions();

        var buildCalibrationForm = function (selectedFDR) {
            that.calibrationWorkspace.append("<div id='calibration-form-content' class='Content is-scrollable'></div>");
            that.calibrationFormContent = $('#calibration-form-content');

            that.calibrationList(selectedFDR);
        }

        dfd.then(buildCalibrationForm);
    };

    this.calibrationFormOptions = function()
    {
        this.calibrationWorkspace
            .append("<div id='calibrationFormOptions' class='OptionsMenu'></div>");
        this.calibrationOptions = $("div#calibrationFormOptions");

        var dfd = $.Deferred();
        var that = this;
        this.getAvaliableFDRs()
            .done(function(avaliableFDRs) {
                var selectedFDR = that.buildCalibrationOptions(avaliableFDRs);
                dfd.resolve(selectedFDR);
            })
            .fail(function () {
                dfd.reject();
            });

        return dfd.promise();
    };

    this.buildCalibrationOptions = function(avaliableFDRs)
    {
        var that = this;
        this.calibrationOptions.empty();
        this.calibrationOptions.append(that.renderCalibrationMenu(avaliableFDRs));

        var $createCalibrationBtn = $('#create-calibration').button();
        var $listCalibrationBtn = $('#list-calibration').button();
        var $fdrCalibrationSelect = $('#fdr-calibration').chosen();

        $fdrCalibrationSelect
            .off('change')
            .change(function(event, params) {
                var selectedFDR = that.getFDRbyId(avaliableFDRs, params.selected);
                that.calibrationList(selectedFDR);
            });

        $createCalibrationBtn
            .off('click')
            .click(function(event) {
                var selectedFDR = that.getFDRbyId(avaliableFDRs, $fdrCalibrationSelect.val());
                that.calibrationCreationForm(selectedFDR);
                $(this).prop('disabled', true);
            });

        $listCalibrationBtn
            .off('click')
            .click(function(event) {
                var selectedFDR = that.getFDRbyId(avaliableFDRs, $fdrCalibrationSelect.val());
                that.calibrationList(selectedFDR);
                $createCalibrationBtn.prop('disabled', false);
            });

        return that.getFDRbyId(avaliableFDRs, $fdrCalibrationSelect.val());
    };

    this.calibrationList = function(selectedFDR)
    {
        this.calibrationFormContent.empty();
        this.calibrationFormContent.append(
            this.renderCalibrationList(selectedFDR)
        );
        this.resizeContainer();
    }

    this.calibrationCreationForm = function(selectedFDR)
    {
        this.calibrationFormContent.empty();
        this.calibrationFormContent.append(
            this.renderCalibrationCreationForm(selectedFDR)
        );
        this.bindEventsCalibrationCreationForm();
        this.resizeContainer();
    }

    this.getAvaliableFDRs = function()
    {
        return $.ajax({
            type : "POST",
            data : {
                action : 'calibration/getAvaliableFdrs',
                data : {
                    dummy : 'data'
                }
            },
            dataType : 'json',
            url : ENTRY_URL,
            async : true
        })
        .fail(function(msg) {
            console.log(msg);
        });
    }

    this.postCalibration = function(fdrId, name, calibrations)
    {
        return $.ajax({
            type : "POST",
            data : {
                action : 'calibration/saveCalibration',
                data : {
                    fdrId: fdrId,
                    name: name,
                    calibrations: calibrations
                }
            },
            dataType : 'json',
            url : ENTRY_URL,
            async : true
        })
        .fail(function(msg) {
            console.log(msg);
        });
    }

    this.getFDRbyId = function(avaliableFDRs, id)
    {
        var fdr;
        for (var ii = 0; ii < avaliableFDRs.length; ii++) {
            if(avaliableFDRs[ii]['id'] === parseInt(id)) {
                fdr = avaliableFDRs[ii]
                break;
            }
        }

        return fdr;
    }

    this.bindEventsCalibrationCreationForm = function() {
        var that = this;
        var removeCalibrationRowButtonBinding  = function () {
            $('.remove-calibration-row-button')
                .off('click')
                .click(function() {
                    $(this).closest('.calibration-row-item').remove();
                });
        };
        removeCalibrationRowButtonBinding();

        var calibrationParamChange = function() {
            $('.calibration-param')
                .off('change')
                .change(function() {
                    that.buildChart($(this).parents('.calibration-param-row'));
                });
        }
        calibrationParamChange();

        $('.remove-calibration-row-button')
            .off('click')
            .click(function() {
                var $row = $(this).parents('.calibration-param-row')
                $(this).closest('.calibration-row-item').remove();
                that.buildChart($row);
            });

        $('.add-calibration-item')
            .off('keyup')
            .keyup(function() {
                var sibling = $(this).closest('.add-calibration-item-row')
                    .siblings('.calibration-table')
                    .children('.calibration-row-item')
                    .last();
                var newSibling = sibling.clone().toggleClass('fill-3')
                newSibling.insertAfter(sibling);

                removeCalibrationRowButtonBinding();
                calibrationParamChange();
            });

        $.each($('.calibration-param-row'), function(index, item) {
            // for async execution
            setTimeout(function() { that.buildChart($(item)); },
                Math.floor(Math.random() * (20 - 5)) + 5
            );
        });

        $('#calibration-name')
            .off('keyup')
            .keyup(function() {
                if ($(this).val().trim() === '') {
                    $('#calibration-save').prop('disabled', true);
                } else {
                    $('#calibration-save').prop('disabled', false);
                }
            });

        $('#calibration-save')
            .off('click')
            .click(function () {
                var name = $('#calibration-name').val().trim();
                if (name !== '') {
                    var calibrations = [];
                    $.each($('.calibration-param-row'), function(index, item) {
                        var $item = $(item);
                        var paramId = $item.data('param-id');

                        var $rows = $item.find('.calibration-row-item');
                        var data = [];

                        for (var ii = 0; ii < $rows.length; ii++) {
                            var $row = $($rows[ii]);
                            data.push(
                                [$row.find('.x-param').first().val(),
                                $row.find('.y-param').first().val()]
                            );
                        }

                        var sortedData = data.sort(that.compareSecondColumn);
                        data = {};
                        for (var ii = 0; ii < sortedData.length; ii++) {
                            data[ii] = {
                                x: sortedData[ii][0],
                                y: sortedData[ii][1]
                            };
                        }

                        calibrations.push({
                            paramId: paramId,
                            points: data
                        });
                    });

                    var fdrId = $('#calibration-creation-form').data('fdr-id');
                    that.postCalibration(fdrId, name, calibrations)
                        .done(function() {
                            that.getAvaliableFDRs()
                                .done(function(avaliableFDRs) {
                                    that.buildCalibrationOptions(avaliableFDRs);
                                    var $fdrCalibrationSelect = $('#fdr-calibration');
                                    var selectedFDR = that.getFDRbyId(avaliableFDRs, fdrId);
                                    that.calibrationList(selectedFDR);
                                });
                        });
                }
            });
    }

    this.buildChart = function($calibrationParamRow) {
        var $rows = $calibrationParamRow.find('.calibration-row-item');
        var data = [];

        for (var ii = 0; ii < $rows.length; ii++) {
            var $row = $($rows[ii]);
            data.push(
                [$row.find('.x-param').first().val(),
                $row.find('.y-param').first().val()]
            );
        }

        $calibrationParamRow.find('.calibration-xy-chart').height(
            $calibrationParamRow.find('.calibration-xy-table').height()
        );
        var ph = $calibrationParamRow.find('.calibration-placeholder');

        $.plot(ph, [data]);
    }

    this.renderCalibrationMenu = function(fdrs) {
        var options = '';

        for (var ii = 0; ii < fdrs.length; ii++) {
            options += '<option value="'+fdrs[ii]['id']+'">'+fdrs[ii]['name']+'</option>'
        }

        return '<table v-align="top">'
            + '<tr>'
            + '<td><label>'+langStr.calibration+'. ' + '</label></td>'
            + '<td><label>'+langStr.calibrationForFDR+': ' + '</label></td>'
            + '<td><div>'
                + '<select id="fdr-calibration">'
                + options
                + '</select>'
            + '</div></td>'
            + '<td><div>'
                + '<button id="list-calibration" class="Button calibration-form-opitons-button">'
                + langStr.calibrationList
                + '</button>'
            + '</div></td>'
            + '<td><div>'
                + '<button id="create-calibration" class="Button calibration-form-opitons-button">'
                + langStr.calibrationCreate
                + '</button>'
            + '</div></td>'
            + '</tr>'
            + '</table>';
    }

    this.renderCalibrationList = function(fdr) {
        if (fdr['calibrations'].length === 0) {
            return '<div>'
                + langStr.calibrationsUnexist
                + '</div>';
        }

        var calibrations = fdr['calibrations'];
        var rows = '';

        for (var ii = 0; ii < calibrations.length; ii++) {
            rows += this.renderCalibrationRow(calibrations[ii]);
        }

        return '<div>'
            + rows
            + '</div>';
    }

    this.renderCalibrationRow = function(calibration) {
        return '<div>' + calibration['id'] + '</div>'
    }

    this.renderCalibrationCreationForm = function(fdr) {
        var paramsTable = this.renderCalibrationParam(fdr['calibratedParams']);

        return '<div id="calibration-creation-form" data-fdr-id="'+fdr['id']+'">'
            + '<div class="row">'
                + '<h3>'+langStr.calibrationCreationForm+'</h3>'
            + '</div>'
            + '<div class="row">'
                + '<h3>'+langStr.calibrationFor + ' ' + fdr.name + '</h3>'
            + '</div>'
            + '<div class="row calibration-name">'
                + '<label class="calibration-form_label">'
                + langStr.calibrationName + " "
                + '<input id="calibration-name" value="" class="calibration-form_input" />'
                + "</label>"
            + '</div>'
            + '<div class="row">'
            + paramsTable
            + '</div>'
            + '<div class="row">'
                + '<button id="calibration-save" class="calibration_button calibration_button__wide" disabled>'
                + langStr.calibrationSave
                + '</button>'
            + '</div>'
            + '</div>';
    }

    this.renderCalibrationParam = function(params) {
        var paramsTable = '';
        for (var ii = 0; ii < params.length; ii++) {
            paramsTable += '<div class="calibration-param-row" data-param-id="'+params[ii].id+'">';

            paramsTable += '<div class="calibration-param-header fill">'
                    + '<div class="calibration-param-cell col-2 bold">'
                    + langStr.calibrationParamName + ': '
                    + '</div>'
                    + '<div class="calibration-param-cell col-3">'
                    + params[ii].name
                    + '</div>'
                    + '<div class="calibration-param-cell col-1 bold">'
                    + langStr.calibrationParamCode + ': '
                    + '</div>'
                    + '<div class="calibration-param-cell col-1">'
                    + params[ii].code
                    + '</div>'
                    + '<div class="calibration-param-cell col-2 bold">'
                    + langStr.calibrationParamChannels + ': '
                    + '</div>'
                    + '<div class="calibration-param-cell col-3">'
                    + params[ii].channel
                    + '</div>'
                + '</div>';

            var calibrationTable = this.renderCalibrationTable(params[ii].xy);

            paramsTable += '<div class="calibration-param-content">'
                    + '<div class="calibration-param-cell calibration-xy-table col-6 right-border top-border">'
                    + calibrationTable
                    + '</div>'
                    + '<div class="calibration-param-cell calibration-xy-chart col-6 top-border">'
                    + '<div class="calibration-placeholder"></div>'
                    + '</div>'
                + '</div>';

            paramsTable += '</div>';
        }

        return paramsTable;
    }

    this.renderCalibrationTable = function(calibration) {
        var calibrationTable = '<div class="calibration-param-header-2 bold fill-2">'
                + '<div class="calibration-param-cell col-5dot5">'
                + 'X'
                + '</div>'
                + '<div class="calibration-param-cell col-5dot5">'
                + 'Y (code)'
                + '</div>'
                + '<div class="calibration-param-cell col-1">'
                + '&nbsp;'
                + '</div>'
            + '</div>';

        calibrationTable += '<div class="calibration-table">';

        for (var jj = 0; jj < calibration.length; jj++) {
            calibrationTable += '<div class="calibration-row-item col-12">'
                    + '<div class="calibration-param calibration-param-cell col-5dot5">'
                        + '<input value="'+calibration[jj].x+'" type="number" class="calibration-param-editing x-param"/>'
                    + '</div>'
                    + '<div class="calibration-param calibration-param-cell col-5dot5">'
                        + '<input value="'+calibration[jj].y+'" type="number" class="calibration-param-editing y-param"/>'
                    + '</div>'
                    + '<div class="calibration-param-cell col-1">'
                        + '<button class="remove-calibration-row-button calibration_button calibration_button__small">'
                        + '&times;'
                        + '</button>'
                    + '</div>'
                + '</div>';
        }

        calibrationTable += '</div>';

        calibrationTable += '<div class="add-calibration-item-row calibration-param-cell col-12 bold">'
                + '<button class="add-calibration-item calibration_button calibration_button__full-width">'
                + langStr.calibrationValueAdd
                + "</button>"
            + "</div>";

        return calibrationTable;
    }

    this.compareSecondColumn = function(a, b)
    {
        if (a[1] === b[1]) {
            return 0;
        } else {
            return a[1] - b[1];
        }
    }
}

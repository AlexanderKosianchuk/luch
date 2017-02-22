function FlightList(langStr, eventHandler)
{
    this.langStr = langStr;

    this.eventHandler = eventHandler;
    this.flightListFactoryContainer = null;
    this.flightListTopMenu = null;
    this.flightListLeftMenu = null;
    this.flightListWorkspace = null;
    this.flightListOptions = null;
    this.flightListContent = null;
}

FlightList.prototype.FillFactoryContaider = function(factoryContainer) {
    var self = this;
    self.flightListFactoryContainer = factoryContainer;

    var pV = {
            action: "flights/flightGeneralElements",
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

            self.flightListFactoryContainer.append(data['topMenu']);
            self.flightListFactoryContainer.append(data['leftMenu']);
            self.flightListFactoryContainer.append(data['fileUploadBlock']);

            self.flightListTopMenu = $('div#topMenuFlightList');
            self.flightListLeftMenu = $('div#leftMenuFlightList');

            self.flightListLeftMenu.on("click", function(e){
                self.leftMenuClick(e);
            });

            self.topMenuUserButtClick();

            self.flightListFactoryContainer.append("<div id='flightListWorkspace' class='WorkSpace'></div>");
            self.flightListWorkspace = $("div#flightListWorkspace");

            self.ShowFlightsListInitial();
            self.TriggerResize();
            self.TriggerUploading();
        } else {
            console.log(answ["error"]);
        }
    });
}

FlightList.prototype.topMenuUserButtClick = function(){
    var self = this,
        userTopButt = $("#userTopButt")

    var fligthOptionsStr = '<ul id="userMenu" class="UserMenuGroup">' +
            '<li id="userOptions">' + this.langStr.options + '</li>' +
            '<li class="UserChangeLang" data-lang="ru">' + "Русский" + '</li>' +
            '<li class="UserChangeLang" data-lang="en">' + "English" + '</li>' +
            '<li class="UserChangeLang" data-lang="es">' + "Español" + '</li>' +
            '<li id="userExit">' + this.langStr.exit + '</li>' +
        '</ul>';

    userTopButt.append(fligthOptionsStr);
    var menu = $("#userMenu").buttonset().menu().hide();

    userTopButt.click(function(e) {
        menu.toggle().position({
             my: "right top",
             at: "right bottom",
             of: this
         });
     });

    $("#userOptions").on("click", function(e){
        self.ShowOptions();
    });

    $("#userExit").on("click", function(e){
        self.eventHandler.trigger("userLogout");
    });

    $(".UserChangeLang").on("click", function(e){
        var lang = $(this).data("lang");
        self.eventHandler.trigger("userChangeLanguage", [lang]);
    });

    $("div#view").on("click", function(e){
        var itemsCheck = $(".ItemsCheck:checked");
        if(itemsCheck.length == 1){
            document.title = itemsCheck.parent().text();
            var itemsCheckType = itemsCheck.data("type");
            if(itemsCheckType = 'flight'){
                var flightId = itemsCheck.data("flightid"),
                    data = [flightId, 'getEventsList', null]
                self.eventHandler.trigger("viewFlightOptions", data);
            }
        }
    });
}

FlightList.prototype.leftMenuClick = function(e){
    var self = this,
    target = $(e.target);

    if(target.attr('id') == "flightLeftMenuRow"){
        if(!target.hasClass('LeftMenuRowSelected')){
            $("#leftMenuFlightList .LeftMenuRowSelected")
                .removeClass('LeftMenuRowSelected', {duration:500});

            target.addClass('LeftMenuRowSelected', {duration:500});

            self.ShowFlightsListInitial();
            self.TriggerResize();
            self.TriggerUploading();
        }
    } else if(target.attr('id') == "searchLeftMenuRow"){
        if(!target.hasClass('LeftMenuRowSelected')){
            $("#leftMenuFlightList .LeftMenuRowSelected")
                .removeClass('LeftMenuRowSelected', {duration:500});

            target.addClass('LeftMenuRowSelected', {duration:500});
            $("div#view").css("display", "none");
            self.eventHandler.trigger("flightSearchFormShow", [self.flightListWorkspace]);
        }
    }  else if(target.attr('id') == "fdrLeftMenuRow"){
        if(!target.hasClass('LeftMenuRowSelected')){
            $("#leftMenuFlightList .LeftMenuRowSelected")
                .removeClass('LeftMenuRowSelected', {duration:500});
            $("div#view").css("display", "none");
            target.addClass('LeftMenuRowSelected', {duration:500});
        }
    } else if(target.attr('id') == "calibrationLeftMenuRow"){
        if(!target.hasClass('LeftMenuRowSelected')){
            $("#leftMenuFlightList .LeftMenuRowSelected")
                .removeClass('LeftMenuRowSelected', {duration:500});
            $("div#view").css("display", "none");
            target.addClass('LeftMenuRowSelected', {duration:500});

            self.eventHandler.trigger("calibrationFormShow", [self.flightListWorkspace]);
        }
    } else if(target.attr('id') == "usersLeftMenuRow"){
        if(!target.hasClass('LeftMenuRowSelected')){
            $("#leftMenuFlightList .LeftMenuRowSelected")
                .removeClass('LeftMenuRowSelected', {duration:500});
            $("div#view").css("display", "none");
            target.addClass('LeftMenuRowSelected', {duration:500});

            self.eventHandler.trigger("userShowList", [self.flightListWorkspace]);
        }
    }
}

FlightList.prototype.ShowFlightViewOptions = function() {
    var self = this;

    if(self.flightListWorkspace != null) {
        self.flightListWorkspace.append("<div id='flightListOptions' class='OptionsMenu'></div>");
        self.flightListOptions = $("div#flightListOptions");

        var fligthOptionsStr = "<table v-align='top'><tr><td><label>" + this.langStr.flightList + " - " + "</label></td><td>";
        fligthOptionsStr +=
            '<div>' +
                '<button id="selectFligthOptionsMenu" class="Button view-options-button">' + this.langStr.initial + '</button>' +
            '</div>' +
            '<ul class="GroupType">' +
                '<li id="treeView">' + this.langStr.treeView + '</li>' +
                '<li id="tableView" style="border:none">' + this.langStr.tableView + '</li>' +
            '</ul></td><td>' +
            '<button id="fileMenu" class="Button">' + this.langStr.fileMenu + '</button>'+
                '<ul class="FileMenuItems">' +
                '</ul>' +
            '</td></tr></table>';

        self.flightListOptions.append(fligthOptionsStr);

         var buttonSelectFligthOptionsMenu = $("button#selectFligthOptionsMenu").button();
         var fileMenu = $('ul.FileMenuItems');
         var fileMenuButt = $("button#fileMenu").button();

         self.fileMenuSupport(fileMenu,
             fileMenuButt,
             [],
             [],
             self
         );

         buttonSelectFligthOptionsMenu.click(function(e) {
             var menu = $(this).parent().next().show().position({
                 my: "left top",
                 at: "left bottom",
                 of: this
             });
             $(document).on("click", function(e) {
                 var target = $(e.target);
                 if(target.attr('id') !== 'byAditionalInfoInput'){
                     menu.hide();
                 }
             });
             return false;
         }).parent()
             .buttonset()
             .next()
             .hide()
             .menu();

         $('#treeView').on("click", function(e) {
             $("div#view").css("display", "none");

             self.ShowFlightsTree();
             buttonSelectFligthOptionsMenu.button({
                  label: self.langStr.treeView
             });
         });

         $('#tableView').on("click", function(e) {
             $("div#view").css("display", "none");

             self.ShowFlightsTable();
             buttonSelectFligthOptionsMenu.button({
                  label: self.langStr.tableView
             });
         });
    }
}

/* ==================================================
 * INITIAL VIEW
 * ================================================== */

FlightList.prototype.ShowFlightsListInitial = function() {
    var self = this;

    if(self.flightListWorkspace != null) {
        self.flightListWorkspace.empty();

        self.ShowFlightViewOptions();

        self.flightListWorkspace.append("<div id='flightListContent' class='Content is-scrollable'></div>");
        self.flightListContent = $("div#flightListContent");

        var pV = {
            action: "flights/getLastView",
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
                    var type = answ['type'];
                    if (type == "flightListTree"){
                        var flightList = answ['data'];
                        self.flightListContent.append(flightList);
                        $("button#selectFligthOptionsMenu").button({
                              label: self.langStr.treeView
                        });
                        self.SupportJsTree();
                        self.ResizeFlightList();
                    } else if (type == "flightListTable"){
                        var flightList = answ['data'],
                            sortCol = answ['sortCol'],
                            sortType = answ['sortType'];
                        self.flightListContent.append(flightList);
                        $("button#selectFligthOptionsMenu").button({
                              label: self.langStr.tableView
                        });
                        self.SupportDataTable(sortCol, sortType);
                        self.ResizeFlightList();
                    }

                } else {
                    console.log(answ);
                    console.log(data['error']);
                }
            }
        }).fail(function(msg){
            console.log(msg);
        });
    }
};

FlightList.prototype.ShowOptions = function() {
    var self = this;
    var form = $('#optionsForm');

    var optionsDialog = $("#optionsDialog").dialog({
        resizable:false,
        autoOpen: true,
        width: '60%',
        modal: true,
        buttons: [
            {
                text: self.langStr.apply,
                click: function() {
                    self.UpdateOptions();
                    optionsDialog.dialog("close");
                }
            },
            {
                text: self.langStr.cancelAction,
                click: function() {
                    optionsDialog.dialog("close")
                }
            }
        ],
        hide: {
            effect: "fadeOut",
            duration: 150
        },
        show: {
            effect: "fadeIn",
            duration: 150
        }
    });
}

FlightList.prototype.UpdateOptions = function() {
    var self = this;
    var msg = $('#optionsForm').serialize();

    return $.ajax({
        type: "POST",
        url: ENTRY_URL,
        dataType: 'json',
        data: {
            action: "user/updateUserOptions",
            data: msg
        }
    });
};

FlightList.prototype.ResizeFlightList = function(e) {
    var self = this;

    var tree = $(".Tree"),
        treeContent = $(".TreeContent");
    if((tree.length > 0) && (treeContent.length > 0)){
        tree.css('height', self.flightListContent.height() - 5);
        treeContent.css('height', self.flightListContent.height() - 5);
    }
}

FlightList.prototype.TriggerResize = function() {
    this.eventHandler.trigger("resizeShowcase");
}

FlightList.prototype.TriggerUploading = function() {
    this.eventHandler.trigger("uploading");
}

FlightList.prototype.ActionOnDblClick = function(sender) {
    var self = this;
    console.log(sender);
    console.log("ActionOnDblClick");
};

FlightList.prototype.ActionChangePath = function(senderType, sender, target) {
    var self = this;

    var pV = {
        action: '',
        data: {
            sender: sender,
            target: target
        }
    };

    if(senderType == 'flight'){
        pV.action = "flights/flightChangePath";
    } else if(senderType == 'folder'){
        pV.action = "flights/folderChangePath";
    }

    return $.ajax({
        type: "POST",
        data: pV,
        url: ENTRY_URL,
        dataType: 'json',
        async: true
    }).fail(function(msg){
        console.log(msg);
    });
};

FlightList.prototype.CreateNewFolder = function(folderName, folderPath) {
    var self = this,
        folderdestination = 0;

    var pV = {
        action: "flights/folderCreateNew",
        data: {
            folderName: folderName,
            fullpath: folderPath
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

FlightList.prototype.RenameFolder = function(folderId, folderName) {
    var self = this;

    var pV = {
        action: "flights/folderRename",
        data: {
            folderId: folderId,
            folderName: folderName
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

FlightList.prototype.ShowFlight = function(id) {
    $("div#flightLeftMenuRow").trigger("showOptions", id);
    return false;
}

/* ==================================================
 * TREE VIEW
 * ================================================== */

FlightList.prototype.ShowFlightsTree = function() {
    var self = this;

    self.flightListContent.slideUp(function(e){
        self.flightListContent.empty();
                var pV = {
                    action: "flights/flightListTree",
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
                            var flightList = answ['data'];
                            self.flightListContent.append(flightList);
                            self.flightListContent.slideDown(function(e){
                                self.SupportJsTree();
                                self.ResizeFlightList(e);
                            });
                        } else {
                            console.log(answ);
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

/*=======================================================================
 * JSTREE SERVICE
 * */
FlightList.prototype.SupportJsTree = function() {
    var self = this,
    contentPlace = $("#jstreeContent");

    var treePrivate = $('#jstree').on("select_node.jstree", function(e, data){
        var selectedjsTreeNode = 0;
        if(data.node.type == 'flight'){
            selectedjsTreeNode = data.node.parent;
        } else {
            selectedjsTreeNode = data.node.id;
        }

        self.ShowContent(selectedjsTreeNode).done(function(answ){
            contentPlace.empty();
            if(answ['status'] == 'ok'){
                var content = answ['data'];
                contentPlace.append(content);
                self.SupportContent.call(self);

                $(".ItemsCheck").on("change", function(e){
                    self.SupportContent.call(self);
                });
            } else {
                console.log(answ)
            }
        });
    }).on('loaded.jstree', function(e, data) {
        // invoked after jstree has loaded
        var node = $('#jstree').jstree('get_selected'),
        selectedjsTreeNode = node[0];

        self.ShowContent(selectedjsTreeNode).done(function(answ){
            contentPlace.empty();
            if(answ['status'] == 'ok'){
                var content = answ['data'];
                contentPlace.append(content);
                self.SupportContent.call(self);

                $(".ItemsCheck").on("change", function(e){
                    self.SupportContent.call(self);
                });
            } else {
                console.log(answ)
            }
        });
    }).on("create_node.jstree", function(e, data){
        var node = data.node,
            parentId = data.parent,
            folderName = node.text;

        self.CreateNewFolder(folderName, parentId).done(function(answ){
            var nodeNewId = answ["data"]['folderId'];
            data.instance.set_id(node, nodeNewId);
            data.instance.set_type(node, "folder");
        });
    }).on("delete_node.jstree", function(e, data){
        var node = data.node,
            type = node.type,
            id = data.node.id;

        self.DeleteItem(type, id).done(function(answ) {
            if(answ['status'] == 'ok'){
                //show root
                var rootNodeId = 0;
                $('#jstree').jstree("select_node", "#" + rootNodeId + "_anchor");
                self.ShowContent(0).done(function(answ){
                    contentPlace.empty();
                    if(answ['status'] == 'ok'){
                        var content = answ['data'];
                        contentPlace.append(content);
                        self.SupportContent.call(self);

                        $(".ItemsCheck").on("change", function(e){
                            self.SupportContent.call(self);
                        });
                    } else {
                        console.log(answ)
                    }
                });
            } else {
                console.log(answ['data']['error']);
            }
        });

    }).on("rename_node.jstree", function(e, data){
        var node = data.node,
        id = node.id,
        folderName = node.text;

        self.RenameFolder(id, folderName).done(function(answ) {
            if(answ['status'] == 'ok'){
                self.ShowContent(id).done(function(answ){
                    contentPlace.empty();
                    if(answ['status'] == 'ok'){
                        var content = answ['data'];
                        contentPlace.append(content);
                        self.SupportContent.call(self);

                        $(".ItemsCheck").on("change", function(e){
                            self.SupportContent.call(self);
                        });
                    } else {
                        console.log(answ)
                    }
                });
            } else {
                console.log(answ['data']['error']);
            }
        });
    }).on("move_node.jstree", function(e, data){
        var node = data.node,
        type = node.type,
        id = node.id,
        newParent = node.parent,
        isNewParentInt =  /^\+?(0|[1-9]\d*)$/.test(newParent);

        if(isNewParentInt){
            var parentNode = $("li#" + newParent).find("a").find("i");

            if(parentNode.hasClass('jstree-folder')){

                self.ActionChangePath(type, id, newParent).done(function(e){
                    self.ShowContent(newParent).done(function(answ){
                        contentPlace.empty();
                        if(answ['status'] == 'ok'){
                            var content = answ['data'];
                            contentPlace.append(content);
                            self.SupportContent.call(self);

                            $(".ItemsCheck").on("change", function(e){
                                self.SupportContent.call(self);
                            });
                        } else {
                            console.log(answ)
                        }
                    });
                });
            } else {
                alert("Incorrect action");
                treePrivate.jstree("refresh");
            }
        }
    }).on("export_node.jstree", function(e, data){
        console.log(e);
        console.log(data);
        console.log("export");
    }).jstree({
        "types" : {
            "folder" : {
                "icon" : "jstree-folder"
            },
            "flight" : {
                "icon" : "jstree-file"
            }
        },
        'core' : {
            'data' : {
                "url" : ENTRY_URL,
                "type": "POST",
                "dataType" : "json", // needed only if you do not supply JSON headers
                "data" : function (node) {
                    var pV = {
                        action : "flights/receiveTree",
                        data : {
                            data : 'data'
                        }
                    };
                    return pV;
                }
            },
            "check_callback" : true
        },
        "plugins" : ["dnd", "types", "contextmenu"],
        "contextmenu": {
            "items": function ($node) {
                var tree = $("#jstree").jstree(true);
                return {
                    "Create": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": self.langStr.jsTree.create,
                        "action": function (obj) {
                            $node = tree.create_node($node);
                            tree.edit($node);
                        }
                    },
                    "Rename": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": self.langStr.jsTree.rename,
                        "action": function (obj) {
                            if($node.type != "flight") {
                                tree.edit($node);
                            } else {
                                return false;
                            }
                        }
                    },
                    "Remove": {
                        "separator_before": false,
                        "separator_after": false,
                        "label":  self.langStr.jsTree.remove,
                        "action": function (obj) {
                            tree.delete_node($node);
                        }
                    },
                    /*"Export": {
                        "separator_before": false,
                        "separator_after": false,
                        "label":  self.langStr.jsTree.export,
                        "action": function (obj) {
                            tree.trigger('export_node', $node);
                        }
                    }*/
                };
            }
        }
    });
}

FlightList.prototype.ShowContent = function(folderId) {
    var self = this,
        pV = {
            action : "flights/showFolderContent",
            data : {
                folderId: folderId
            }
        };

    return $.ajax({
        url: ENTRY_URL,
        type: "POST",
        data: pV,
        dataType: "json",
        async: true
    }).fail(function(e){
        console.log(e);
    });
}

FlightList.prototype.SupportContent = function() {
    var checked = $(".ItemsCheck:checked"),
        fileMenu = $('ul.FileMenuItems'),
        fileMenuButt = $("button#fileMenu"),
        folders = new Array(),
        flights = new Array();

    $.each(checked, function(i, el){
        var el = $(el);
        if(el.data('type') == 'flight'){
            flights.push(el);
        } else if(el.data('type') == 'folder') {
            folders.push(el);
        }
    });

    this.fileMenuSupport(fileMenu,
        fileMenuButt,
        flights,
        folders,
        this
    );
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
                                self.ResizeFlightList(e);
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
        "order": [[ sortColumn, sortType]],
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
                self.SupportContent.call(self);

                $(".ItemsCheck").on("change", function(e){
                    self.SupportContent.call(self);
                });
            })
            .fail(function(a){
                console.log(a);
            });
        },
        "oLanguage": self.langStr.dataTable,
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

FlightList.prototype.fileMenuSupport = function(
    fileMenu,
    fileMenuButt,
    flights,
    folders,
    self
) {
    $('#view').hide();
    if((flights.length == 1) && (folders.length == 0)){
        fileMenu.empty();
        fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
        fileMenu.append('<li id="selectAll">' + self.langStr.selectAll + '</li>');
        fileMenu.append('<li id="export">' + self.langStr.exportItem + '</li>');
        fileMenu.append('<li id="process">' + self.langStr.processItem + '</li>');
        fileMenu.append('<li id="exportCoordinates">' + self.langStr.exportCoordinates + '</li>');
        fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');

        $('#view').show();
    } else if((flights.length == 0) && (folders.length == 1)){
        fileMenu.empty();
        fileMenu.append('<li id="open">' + self.langStr.openItem + '</li>');
        fileMenu.append('<li id="rename">' + self.langStr.renameItem + '</li>');
        fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
        fileMenu.append('<li id="selectAll">' + self.langStr.selectAll + '</li>');
        fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
    } else if((flights.length > 1) && (folders.length == 0)){
        fileMenu.empty();
        fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
        fileMenu.append('<li id="selectAll">' + self.langStr.selectAll + '</li>');
        fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
    } else if((flights.length == 0) && (folders.length > 1)){
        fileMenu.empty();
        fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
        fileMenu.append('<li id="selectAll">' + self.langStr.selectAll + '</li>');
        fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
    } else if((flights.length >= 1) && (folders.length >= 1)){
        fileMenu.empty();
        fileMenu.append('<li id="delete">' + self.langStr.deleteItem + '</li>');
        fileMenu.append('<li id="selectAll">' + self.langStr.selectAll + '</li>');
        fileMenu.append('<li id="removeSelection" style="border:none;">' + self.langStr.removeSelection + '</li>');
    } else {
        fileMenu.empty();
        fileMenu.append('<li id="selectAll" style="border:none;">' + self.langStr.selectAll + '</li>');
    }

    fileMenu
        .buttonset()
        .hide()
        .menu();

    fileMenuButt.button().click(function() {
         var menu = $(this).next().show().position({
             my: "left top",
             at: "left bottom",
             of: this
         });
         $(document).on("click",function(e) {
             menu.hide();
         });
         return false;
     });

         $("li#open").off('click').on('click', function(e){
             var inputItemsCheck = $(".ItemsCheck:checked"),
             folderId = inputItemsCheck.data('folderdestination'),
             contentPlace = $("#jstreeContent");
             self.ShowContent(folderId).done(function(answ){
                 contentPlace.empty();
                 if(answ['status'] == 'ok'){
                     var content = answ['data'];
                     contentPlace.append(content);
                     self.SupportContent.call(self);

                     $(".ItemsCheck").on("change", function(e){
                         self.SupportContent.call(self);
                     });
                 } else {
                     console.log(answ)
                 }
             });
         });

         $("li#rename").off('click').on('click', function(e){
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
         });

         $("li#removeSelection").off('click').on('click', function(e){
             $.each($("input.ItemsCheck:checked"), function(i, el){
                 var el = $(el).prop('checked', false);
             });
             self.SupportContent.call(self);
         });

         $("li#selectAll").off('click').on('click', function(e){
             $.each($(".ItemsCheck"), function(i, el){
                 var el = $(el).prop('checked', true);
             });
             self.SupportContent.call(self);
         });

         $("li#delete").off('click').on('click', function(e){
             var inputItemsCheck = $("input.ItemsCheck:checked");
             var deletedCount = 0;

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
         });

         $("li#process").off('click').on('click', function(e){
             var inputItemsCheck = $("input.ItemsCheck:checked");

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
         });

         $("li#export").off('click').on('click', function(e){
             var inputItemsCheck = $("input.ItemsCheck:checked");
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
         });

         if($("li#exportCoordinates").length) {
             var text = $("li#exportCoordinates").text();

             var inputItemsCheck = $("input.ItemsCheck:checked");

             if (inputItemsCheck.length) {
                 var flightid = inputItemsCheck.data('flightid');

                 $("li#exportCoordinates")
                    .empty()
                    .append(
                         $('<a></a>', {
                             class: 'export-coordinates-href',
                             href: '/view/flights.php?action=coordinates&id=' + flightid,
                             target: '_blank'
                         }).text(text)
                     );
             }
         }
}

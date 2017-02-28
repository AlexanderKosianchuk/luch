///==================================================
//GENERAL INFO
///==================================================
function FdrGeneralInfo(langStr, eventHandler, bruTypeListFactoryContainer) {
    var langStr = langStr,
        eventHandler = eventHandler;

    var bruTypeId = null;

    var factoryWindow = bruTypeListFactoryContainer,
        bruTypeTopMenu = null,
        bruTypeListWorkspace = null,
        bruTypeListOptions = null,
        bruTypeListContent = null;

    ///
    // PRIVATE
    ///
    var ShowGeneralInfoOptions = function() {
        bruTypeListWorkspace.append("<div id='bruTypeOptions' class='OptionsMenu'></div>");
        bruTypeListOptions = $("div#bruTypeOptions");

        var fligthOptionsStr = '<table v-align="top"><tr>' +
            '<td><label>' + langStr.bruTypeLabel + " - " + '</label></td><td>' +
            '<div>' +
                '<button id="createBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.createType + '</button>' +
            '</div>' +
            '</td><td>' +
            '<div>' +
                '<button id="copyBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.copyType + '</button>' +
            '</div>' +
            '</td><td>' +
            '<div>' +
                '<button id="saveBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.saveType + '</button>' +
            '</div>' +
            '</td><td>' +
            '<div>' +
                '<button id="delBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + langStr.deleteType + '</button>' +
            '</div>' +
            '</td></tr></table>';

        bruTypeListOptions.append(fligthOptionsStr);

        $("div#bruTypeOptions .Button").button();
    },

    ShowGeneralInfoContent = function() {
        var self = this;
    };


    ///
    // PRIVILEGED
    ///
    this.Show = function(extBruTypeId, extBruTypeTopMenu, extBruTypeListWorkspace) {
        bruTypeId = extBruTypeId;
        bruTypeTopMenu = extBruTypeTopMenu;
        bruTypeListWorkspace = extBruTypeListWorkspace;

        bruTypeListWorkspace.empty();

        ShowGeneralInfoOptions();
        ShowGeneralInfoContent();
    }

}

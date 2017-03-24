///==================================================
//GENERAL INFO
///==================================================
function FdrCyclo(langStr) {
    this.langStr = langStr;

    this.bruTypeId = null;

    this.bruTypeListWorkspace = null;
    this.bruTypeListOptions = null;
    this.bruTypeListContent = null;
}

FdrCyclo.prototype.ShowGeneralInfoOptions = function() {
    var self = this;

    self.bruTypeListWorkspace.append("<div id='bruTypeOptions' class='OptionsMenu'></div>");
    self.bruTypeListOptions = $("div#bruTypeOptions");

    var fligthOptionsStr = '<table v-align="top"><tr>' +
        '<td><label>' + this.langStr.bruTypeLabel + " - " + '</label></td><td>' +
        '<div>' +
            '<button id="createBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + this.langStr.createType + '</button>' +
        '</div>' +
        '</td><td>' +
        '<div>' +
            '<button id="copyBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + this.langStr.copyType + '</button>' +
        '</div>' +
        '</td><td>' +
        '<div>' +
            '<button id="saveBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + this.langStr.saveType + '</button>' +
        '</div>' +
        '</td><td>' +
        '<div>' +
            '<button id="delBruTypeBut" class="Button" style="margin-right:1px; min-width:155px;">' + this.langStr.deleteType + '</button>' +
        '</div>' +
        '</td></tr></table>';

    self.bruTypeListOptions.append(fligthOptionsStr);

    $("div#bruTypeOptions .Button").button();
}

FdrCyclo.prototype.ShowGeneralInfoContent = function() {
    var self = this;
}

module.exports = FdrCyclo;

var SERVICE_STRS = location.protocol + '//' + location.host + "/config/" + "_actions.json",

    LANG_FILES_PATH = location.protocol + '//' + location.host + "/lang/",
    LANG_FILE_DEFAULT =  location.protocol + '//' + location.host + "/lang/" + "EN.lang";

function Language(selectedLang) {
    this.selectedLang = selectedLang;
}

Language.prototype.GetLanguage = function() {
    var path = LANG_FILES_PATH + this.selectedLang.toUpperCase() + ".lang",
        lang = Object();

    return $.ajax({
        url: path,
        dataType: 'json',
        async: true,
        success: function(data) {
            lang = data;
        }
    }).fail(function() {
        $.ajax({
            url: LANG_FILE_DEFAULT,
            dataType: 'json',
            async: true,
            success: function(data) {
                lang = data;
            }
        }).fail(function() {
            console.log("Cant get lang object.");
        });
    });
};

module.exports = Language;

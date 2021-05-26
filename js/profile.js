const App = {};

//funkce na vytazeni GET parametru z url
let getUrlParameter = function getUrlParameter(sParam) {
    let sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};

//kdyz se nacte stranka
$( document ).ready(function() {
    App.uploadWrapPicture = $(`#user-upload-wrap-pp`);
    App.uploadWrapBanner = $(`#user-upload-wrap-banner`);
    App.pictureEditButton = $(`#user-header-picture-edit`);
    App.bannerEditButton = $(`#user-header-banner-edit`);
    App.banner = $(`#user-header-banner`);

    App.uploadWrapBanner.css("display", "none");
    App.uploadWrapPicture.css("display", "none");

    App.pictureEditButton.click(() => {
        App.uploadWrapBanner.css("display", "none");
        App.uploadWrapPicture.css("display", "flex");

        const uploadCloseButton = $(`#file-upload-close-pp`);
        uploadCloseButton.click(() => {
            App.uploadWrapBanner.css("display", "none");
            App.uploadWrapPicture.css("display", "none");
        });
    });

    App.bannerEditButton.click(() => {
        App.uploadWrapBanner.css("display", "flex");
        App.uploadWrapPicture.css("display", "none");

        const uploadCloseButton = $(`#file-upload-close-banner`);
        uploadCloseButton.click(() => {
            App.uploadWrapBanner.css("display", "none");
            App.uploadWrapPicture.css("display", "none");
        });
    });

    //jelikoz je pozadi na profilu pozadi elementu, nejde ho nastavit v php (cesta k souboru se nastavuje v css), proto musime zjistit soubor uzivatelova pozadi pres rest api
    //odeslat data o novem zamestnanci
    let jsonData = {
        "name": getUrlParameter('user')
    };
    let settings = {
        "async": true,
        "crossDomain": false,
        "url": "get_user_banner.php",
        "method": "POST",
        "headers": {
            "content-type": "application/json",
            "cache-control": "no-cache"
        },
        "data": JSON.stringify(jsonData)
    }
    $.ajax(settings).done((data) => {
        if (data.res !== 'notfound') {
            App.banner.css("background-image", 'url(' + data.res + ')');
        }
    });
});
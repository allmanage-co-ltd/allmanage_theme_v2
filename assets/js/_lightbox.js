/*********************************************
 * lightbox
 *
 * https://qiita.com/TMTN/items/1b834aebba3884010689
 * https://ryob.net/how-to-use-lightbox/
 *
 ********************************************/
$(function () {
    lightbox.option({
        'alwaysShowNavOnTouchDevices': false,
        // 'albumLabel': 'ギャラリー： %1 of %2',
        'disableScrolling': true,
        'fadeDuration': 600,
        'fitImagesInViewport': true,
        'imageFadeDuration': 600,
        // 'maxWidth': 400,
        // 'maxHeight': 400,
        // 'positionFromTop': 50,
        'resizeDuration': 0,
        'showImageNumberLabel': true,
        'wrapAround': false,
    });
});

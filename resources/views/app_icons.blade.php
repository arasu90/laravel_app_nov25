<?php
$icons = [
"fa-glass","fa-music","fa-search","fa-envelope-o","fa-heart","fa-star","fa-star-o",
"fa-user","fa-film","fa-th-large","fa-th","fa-th-list","fa-check","fa-times",
"fa-search-plus","fa-search-minus","fa-power-off","fa-signal","fa-cog","fa-trash-o",
"fa-home","fa-file-o","fa-clock-o","fa-road","fa-download","fa-inbox",
"fa-refresh","fa-lock","fa-flag","fa-headphones","fa-volume-off","fa-volume-down",
"fa-volume-up","fa-qrcode","fa-barcode","fa-tag","fa-tags","fa-book","fa-bookmark",
"fa-print","fa-camera","fa-font","fa-bold","fa-italic","fa-text-height",
"fa-text-width","fa-align-left","fa-align-center","fa-align-right","fa-align-justify",
"fa-list","fa-indent","fa-video-camera","fa-picture-o","fa-pencil","fa-map-marker",
"fa-adjust","fa-tint","fa-edit","fa-share-square-o","fa-check-square-o","fa-arrows",
"fa-play","fa-pause","fa-stop","fa-forward","fa-backward","fa-plus","fa-minus",
"fa-asterisk","fa-exclamation-circle","fa-gift","fa-leaf","fa-fire","fa-eye",
"fa-eye-slash","fa-warning","fa-plane","fa-calendar","fa-comment","fa-magnet",
"fa-retweet","fa-shopping-cart","fa-folder","fa-folder-open","fa-bar-chart",
"fa-twitter","fa-facebook","fa-camera-retro","fa-key","fa-comments","fa-thumbs-o-up",
"fa-thumbs-o-down","fa-star-half","fa-heart-o","fa-sign-out","fa-linkedin",
"fa-thumb-tack","fa-external-link","fa-sign-in","fa-trophy","fa-github",
"fa-upload","fa-lemon-o","fa-phone","fa-square-o","fa-bookmark-o","fa-phone-square",
"fa-unlock","fa-credit-card","fa-rss","fa-hdd-o","fa-bullhorn","fa-bell",
"fa-certificate","fa-globe","fa-wrench","fa-tasks","fa-filter","fa-briefcase",
"fa-arrows-alt","fa-users","fa-link","fa-cloud","fa-flask","fa-scissors",
"fa-files-o","fa-paperclip","fa-save","fa-bars","fa-list-ul","fa-list-ol",
"fa-table","fa-magic","fa-truck","fa-money","fa-envelope","fa-undo","fa-gavel",
"fa-dashboard","fa-comment-o","fa-comments-o","fa-bolt","fa-sitemap","fa-umbrella",
"fa-clipboard","fa-lightbulb-o","fa-exchange","fa-cloud-download","fa-cloud-upload",
"fa-user-md","fa-stethoscope","fa-suitcase","fa-coffee","fa-cutlery",
"fa-file-text-o","fa-building-o","fa-hospital-o","fa-ambulance","fa-medkit",
"fa-fighter-jet","fa-beer","fa-h-square","fa-plus-square","fa-desktop","fa-laptop",
"fa-tablet","fa-mobile","fa-spinner","fa-circle","fa-reply","fa-github-alt",
"fa-folder-o","fa-folder-open-o","fa-smile-o","fa-frown-o","fa-meh-o","fa-gamepad",
"fa-keyboard-o","fa-terminal","fa-code","fa-bug","fa-sun-o","fa-moon-o",
"fa-archive","fa-wheelchair","fa-space-shuttle","fa-motorcycle","fa-street-view",
"fa-heartbeat","fa-whatsapp","fa-server","fa-user-plus","fa-user-times","fa-bed",
"fa-train","fa-subway","fa-medium","fa-battery-full","fa-mouse-pointer",
"fa-object-group","fa-object-ungroup","fa-sticky-note","fa-clone","fa-balance-scale",
"fa-hourglass","fa-handshake-o","fa-envelope-open","fa-address-book",
"fa-address-card","fa-user-circle","fa-id-card","fa-telegram","fa-thermometer",
"fa-shower","fa-bath","fa-podcast","fa-window-maximize","fa-window-close",
"fa-bandcamp","fa-imdb","fa-snowflake-o","fa-microchip"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Font Awesome 4.7 – All Icons (PHP)</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
}
#icons {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}
.icon {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
    border-radius: 6px;
    font-size: 13px;
}
.icon i {
    font-size: 24px;
    display: block;
    margin-bottom: 8px;
}
</style>
</head>
<body>

    <a href="/">Go Back</a>
    <h2>Font Awesome 4.7 - All Icons</h2>
    <div id="icons">
        <?php foreach ($icons as $icon): ?>
            <div class="icon">
                <i class="fa <?= $icon ?>"></i>
                <?= $icon ?>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>

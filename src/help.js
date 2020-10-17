/* Da Redaxo tw. mit pjax arbeitet, muss prism in diesem Fall "von Hand" aktiviert werden. */
/*
    Erweitert prims.js (code-Blöcke in der Doku formatieren)
    um einige weitere Einstellungen zur Redaxo-Kompatibilität

    muss per asset_packer bei der Installation mit prism gemischt werden.
*/
$(document).on('rex:ready',function(event,container){
    Prism.highlightAll();
});

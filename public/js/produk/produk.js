$(function () {

    // Product-table scripts are loaded by the shared layout.  Do not run them
    // on unrelated pages (such as Monitoring), where product configuration is
    // intentionally unavailable.
    if (!window.produkConfig || !document.getElementById('produkTable')) {
        return;
    }

    initClientTable();
    initSelect2();
    initHierarchy();
    initPlatform();
});

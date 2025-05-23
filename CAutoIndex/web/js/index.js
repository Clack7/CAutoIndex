/**
 * CAutoIndex v1.0
 *
 * Copyright (C) 2013 Claudio Andrés Rivero <riveroclaudio@ymail.com>
 */
$(function() {
    var ajax, init = false, fancyImg = false, orderBy = ['e', 1], stateChange = true;
    var baseUrl = location.protocol + '//' + location.hostname;
    $('.container').delegate('.folder', 'click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href').replace(baseUrl, '').substring(subDir.length);
        get(url, $(this).data('in'));
    });

    function get(url, inFx, force, newState) {
        if (url.substr(url.length - 1) != '/') { url += '/'; }
        if (init && current[0] === url && !force) { return; }

        if (ajax) { ajax.abort(); }
        $('#list .panel-loader').fadeIn(300);
        ajax = $.get(
            '/' + subDir + '?path=' + url,
            function (data) {
                $('#list .panel-loader').fadeOut(300);
                if (data.list) {
                    current = data.current;
                    init = true;

                    var links = '<a href="/' + subDir + '" class="folder" data-in="0">' + rootDir + '</a>',
                        route = subDir ? '/' + subDir.substring(0, subDir.length - 1) : '';
                    var last = data.parts.pop();
                    for (var i in data.parts) {
                        route += '/' + data.parts[i][0];
                        links += ' / <a href="' + route + '" class="folder" data-in="0">' + data.parts[i][1] + '</a>';
                    }
                    links += last ? ' / ' + last[1] : '';

                    var h1 = $('h1');
                    var nh = $('<h1>' + links + '</h1>').hide();

                    $('#list .panel-heading .panel-title').text(last ? last[1] : rootDir);
                    $('#list .panel-heading > span').text(data.info ? data.info : '');

                    h1.parent().append(nh);
                    h1.fadeOut(500, function() { $(this).remove(); });
                    nh.fadeIn(500, function() { $(this).parent().animate({ height: $(this).outerHeight() + 19 }); });

                    var table = $('#list .table-responsive');

                    if (inFx !== false) {
                        var w = table.width();
                        var l = inFx ? w : (w * -1);
                        table.width(w);
                        var list = $(data.list);
                        list.css({ width: w, left: l });
                        $('#list').css('overflow', 'hidden').append(list);
                        orderRows(list);

                        table.animate({ left: (l * -1) }, 500, function() { $(this).remove(); });
                        list.animate({ left: 0 }, 500, function() {
                            $(this).removeAttr('style');
                            initFancyBox();
                        });
                        $('#list').animate({ height: (list.outerHeight() + 41) }, 500, function() {
                            $(this).css('overflow', 'visible');
                        });
                    } else {
                        table.remove();
                        $('#list').append(data.list);
                        orderRows();
                    }

                    $('body,html').animate({scrollTop: 0}, 500);

                    stateChange = false;
                    if (newState !== false) {
                        History.pushState(
                            {urlPath: current[0]},
                            getTitle(),
                            '/' + subDir.substring(0, subDir.length - 1) + current[0]
                        );
                    }
                    stateChange = true;
                }
            }
        );
    }

    $( document ).ajaxError(function( event, jqxhr, settings ) {
        if (jqxhr.statusText === 'abort') { return; }
        $.pnotify({
            title: '<strong><span class="glyphicon glyphicon-fire"></span> ' + jqxhr.status + ' ' + jqxhr.statusText + '</strong>',
            text: settings.url,
            type: 'error',
            shadow: false
        });
        $('#list .panel-loader').fadeOut(300);
    });

    if (window.location.hash && window.location.hash !== '#' && window.location.hash.substring(0, 4) !== '#./?') {
        var url = window.location.hash.substring(1);
        url = decodeURIComponent(decodeURIComponent(url.split('?')[0].replace('./', '')));
        if (url.substring(0, 1) != '/') { url = current[0] + '/' + url; }
        if (url != '/' && url.substring(url.length - 1) == '/') { url = url.substring(0, url.length - 1); }
        if (url != '' && url != current[0]) {
            History.replaceState(
                {urlPath: url}, getTitle(url),
                '/' + subDir.substring(0, subDir.length - 1) + url
            );
            get(url, 1, false, false);
        }
    } else {
        $('#list').css('overflow', 'hidden').animate({ height: ($('#list table:first').outerHeight() + 41) }, 500, function() {
            $(this).css('overflow', 'visible');
            if (current[0] !== '/') {
                document.title = getTitle();
                $('#list .panel-heading .panel-title').text(current[1].split('/').pop());
            }
        });
        orderRows();
        headerResize();

        History.replaceState(
            {urlPath: current[0]},
            current[0] !== '/' ? getTitle() : document.title, window.location.pathname
        );
    }

    History.Adapter.bind(window, 'statechange', function() {
        if (!stateChange) { return; }
        var State = History.getState();
        var inFx = State.data.urlPath.length >= current[0].length ? 1 : 0;
        get(State.data.urlPath, inFx, false, false);
    });

    $('#list').delegate('th a', 'click', function(e) {
        e.preventDefault();

        var order = $(this).data('order');
        var asc = order == orderBy[0] ? (orderBy[1] == 1 ? 0 : 1) : 1;
        orderBy = [order, asc];

        orderRows();
    });

    function getTitle(path) {
        path = path ? path : current[1];

        if (path == '/') {
            return rootDir;
        }

        path  = path.substring(path.length - 1) == '/' ? path.substring(0, path.length - 1) : path;
        path  = path.split('/');
        title = path.pop();
        path  = path.join('/');

        return title + (path == '' ? '' : ' - ' + path);
    }

    function orderRows(list) {
        var table = list ? $(list) : $('#list table:last');
        var rows  = table.find('tbody tr').get();
        rows.sort(function(a, b) {
            var keyA = $(a).data('s' + orderBy[0]);
            var keyB = $(b).data('s' + orderBy[0]);
            if (keyA < keyB) { return orderBy[1] ? -1 : 1; }
            if (keyA > keyB) { return orderBy[1] ? 1 : -1; }
            return 0;
        });

        $.each(rows, function(index, row) {
            table.find('tbody').append(row);
        });

        table.find('th a span').remove();
        table.find('th a[data-order="' + orderBy[0] + '"]')
             .append(' <span class="glyphicon glyphicon-chevron-' +
                     (orderBy[1] == 1 ? 'down' : 'up') + '"></span>');
    }

    function headerResize() {
        $('#wrap .container .page-header:first').animate({ height: $('h1').outerHeight() + 19 }, 450);
    }

    function initFancyBox() {
        $(".showImage").fancybox({
            padding: 0,
            openEffect : 'elastic',
            openSpeed  : 150,
            closeEffect : 'elastic',
            closeSpeed  : 150,
            closeClick : true,
            closeBtn: false,
            minHeight: 'none',
            minWidth: 'none',
            afterShow: function() { fancyImg = true; },
            beforeClose: function() { fancyImg = false; },
            helpers: { overlay: { locked: false } }
        });

        $(".showIframe, .showCode").fancybox({
            padding: 0,
            openEffect : 'elastic',
            openSpeed  : 150,
            closeEffect : 'elastic',
            closeSpeed  : 150,
            closeClick : true,
            closeBtn: false,
            minHeight: 400,
            helpers: { overlay: { locked: false } }
        });

        // Tooltip lazy load
        $(".open, .showCode, .showImage, .showIframe").on('mouseenter', function() {
            if ($(this).data('tooltip')) {
                return;
            }
            $(this).data('tooltip', 1);
            $(this).tooltip({ container: 'body' }).tooltip('show');
        });
    }
    initFancyBox();

    $(window).bind('mousewheel', function(e, delta) {
        if (fancyImg && e.target.className === 'fancybox-overlay fancybox-overlay-fixed') {
            if (delta < 0) {
                $.fancybox.next('up');
            } else {
                $.fancybox.prev('down');
            }
            e.preventDefault();
        }
    });

    var wResTo;
    $(window).resize(function() {
        clearTimeout(wResTo);
        wResTo = setTimeout(function() {
            headerResize();
        }, 500);
    });
});
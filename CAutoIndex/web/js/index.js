/**
 * CAutoIndex v1.0
 *
 * Copyright (C) 2013 Claudio Andrés Rivero <riveroclaudio@ymail.com>
 */
$(function() {
    var ajax, addrOn = false, init = false, fancyImg = false, order = '';
    var baseUrl = location.protocol+'//'+location.hostname;
    $('.container').delegate('.folder', 'click', function(e) {
        e.preventDefault();
        get($(this).attr('href'), $(this).data('in'));
    });

    $.address.change(function(e) {
        if (addrOn) {
            var inFx = e.path.length > current.length ? 1 : 0;
            get(e.path, inFx);
        }
    });
    
    function get(url, inFx, force) {
        if (subDir) { url = url.replace(subDir, ''); }
        url = url.replace(baseUrl, '');
        if (init && current === url && !force) { return; }
      
        if (ajax) { ajax.abort(); }
        $('#list .panel-loader').fadeIn(300);
        ajax = $.get(
            '/' + subDir + '?path=' + url + (order ? '&' + order : ''),
            function (data) {
                $('#list .panel-loader').fadeOut(300);
                if (data.list) {
                    current = url.split("&")[0];
                    init = true;
                    
                    var links = '<a href="/' + subDir + '" class="folder" data-in="0">' + rootDir + '</a>', route = '';
                    var last = data.parts.pop();
                    for (var i in data.parts) {
                        route += '/' + data.parts[i];
                        links += ' / <a href="' + route + '" class="folder" data-in="0">' + data.parts[i] + '</a>';
                    }
                    links += last ? ' / ' + last : '';

                    var h1 = $('h1');
                    var nh = $('<h1>' + links + '</h1>').hide();
                    
                    $('#list .panel-heading .panel-title').text(last ? last : rootDir);
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
                    }
                    
                    $('body,html').animate({scrollTop: 0}, 500);
                    
                    addrOn = false;
                    window.location.hash = url;
                    addrOn = true;
                    
                    document.title = current === '/' ? rootDir : current;
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
    
    if (window.location.hash && window.location.hash !== '#' && window.location.hash !== '#' + current) {
        get(window.location.hash.substring(1), 1);
    } else {
        $('#list').css('overflow', 'hidden').animate({ height: ($('#list table:first').outerHeight() + 41) }, 500, function() {
            $(this).css('overflow', 'visible');
            if (current !== '/') { document.title = current; }
            addrOn = true;
        });
        headerResize();
    }
    
    $('#list').delegate('th a', 'click', function(e) {
        e.preventDefault();

        var cls = 'glyphicon glyphicon-chevron-';
        var span = $(this).find('span');
        if (span.length < 1) {
            $('#list th a span').remove();
            $(this).append(' <span class="' + cls + 'down"></span>');
            order = 'ord=' + $(this).data('name') + '&asc=1';
        } else if (span.hasClass(cls + 'down')) {
            span.removeClass(cls + 'down').addClass(cls + 'up');
            order = 'ord=' + $(this).data('name') + '&asc=0';
        } else {
            span.removeClass(cls + 'up').addClass(cls + 'down');
            order = 'ord=' + $(this).data('name') + '&asc=1';
        }
        
        get(current, false, true);
    });

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
        
        $(".open, .showCode, .showImage, .showIframe").tooltip({ container: 'body' });
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
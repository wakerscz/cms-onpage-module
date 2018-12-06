/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


$(function ()
{
    var timeout = 1000 * 3;

    setTimeout(function ()
    {
        var title = $('title').text().trim();
        var ogTitle = $('meta[property="og:title"]');

        if (ogTitle.length === 0)
        {
            $.notification(
                'warning',
                $.i18nGet('social-sites'),
                $.i18nGet('set-social-sites')
            )
        }

        if (title.includes('Wakers CMS') && title.length === 14)
        {
            $.notification(
                'warning',
                $.i18nGet('search-engines'),
                $.i18nGet('set-search-engines')
            )
        }

    }, timeout);

});
/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function($, window, document, _undefined)
{
    XF.adjustHtmlForRteOld = XF.adjustHtmlForRte;
    XF.adjustHtmlForRte = function(content)
    {
        content = XF.adjustHtmlForRteOld(content);

        content = content.replace(/([\w\W]|^)<a\s[^>]*data-usergroup-id="\d+"\s+data-groupname="([^"]+)"[^>]*>([\w\W]+?)<\/a>/gi,
            function(match, prefix, user, username) {
                return prefix + (prefix === '@' ? '' : '@') + username.replace(/^@/, '');
            }
        );

        return content;
    };
}(jQuery, window, document));

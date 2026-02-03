(function(window, document, _undefined)
{
    var adjustHtmlForRteOld = XF.adjustHtmlForRte;
    XF.adjustHtmlForRte = function(content)
    {
        content = adjustHtmlForRteOld(content);

        content = content.replace(/([\w\W]|^)<a\s[^>]*data-usergroup-id="\d+"\s+data-groupname="([^"]+)"[^>]*>([\w\W]+?)<\/a>/gi,
            function(match, prefix, user, username) {
                return prefix + (prefix === '@' ? '' : '@') + username.replace(/^@/, '');
            }
        );

        return content;
    };
}(window, document));

<?php

namespace SV\UserMentionsImprovements\XF\Repository;

\SV\StandardLib\Helper::repo()->aliasClass(
    'SV\UserMentionsImprovements\XF\Repository\UserAlert',
    \XF::$versionId < 2020000
        ? 'SV\UserMentionsImprovements\XF\Repository\XF2\UserAlert'
        : 'SV\UserMentionsImprovements\XF\Repository\XF22\UserAlert'
);

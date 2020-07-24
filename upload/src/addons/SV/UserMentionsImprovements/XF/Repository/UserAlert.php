<?php

namespace SV\UserMentionsImprovements\XF\Repository;

if (\XF::$versionId < 2020000)
{
    \class_alias('SV\UserMentionsImprovements\XF\Repository\XF2\UserAlert', 'SV\UserMentionsImprovements\XF\Repository\UserAlert');
}
else
{
    \class_alias('SV\UserMentionsImprovements\XF\Repository\XF22\UserAlert', 'SV\UserMentionsImprovements\XF\Repository\UserAlert');
}
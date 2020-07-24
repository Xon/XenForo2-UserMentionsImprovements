<?php

namespace SV\UserMentionsImprovements\XF\Repository;

if (\XF::$versionId < 2020000)
{
    class UserAlert extends \SV\UserMentionsImprovements\XF\Repository\XF2\UserAlert
    {

    }
}
else
{
    class UserAlert extends \SV\UserMentionsImprovements\XF\Repository\XF22\UserAlert
    {

    }
}
<?php


namespace SV\UserMentionsImprovements\Option;

use XF\Entity\Option;

class MiniAvatar
{
    public static function verifyDisplayGroupAvatar(&$value, Option $option)
    {
        if ($option->option_value !== $value)
        {
            /** @var \XF\Repository\Style $styleRepo */
            $styleRepo = \XF::app()->repository('XF:Style');
            $styleRepo->updateAllStylesLastModifiedDate();
        }

        return true;
    }
}

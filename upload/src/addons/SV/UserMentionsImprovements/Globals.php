<?php

namespace SV\UserMentionsImprovements;

/**
 * Add-on globals.
 */
abstract class Globals
{
    /**
     * @var array|null
     */
    public static $userGroupMentionedIds = null;

    /**
     * Private constructor, use statically.
     */
    private function __construct() { }
}

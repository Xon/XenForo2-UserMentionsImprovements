<?php

namespace SV\UserMentionsImprovements\bbCode;

use XF\BbCode\Renderer\AbstractRenderer;
use XF\BbCode\Renderer\EditorHtml;
use XF\BbCode\Renderer\Html;

class Listener
{
    /**
     * Extend bbcode rendering to include user group tags and user icons
     *
     * @param AbstractRenderer $renderer
     * @param string           $type
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function bbCodeRender(AbstractRenderer $renderer, string $type)
    {
        if ($renderer instanceof EditorHtml)
        {
            $renderer->addTag(
                'usergroup',
                [
                    'replace'             => null,
                    'callback'            => null,
                    'trimAfter'           => 0,
                    'stopBreakConversion' => false,
                ]
            );
        }
        else if ($renderer instanceof Html)
        {
            $class = \XF::app()->extendClass(tagRenderer::class);
            /** @var tagRenderer $obj */
            $obj = new $class($renderer, $type);
            $obj->bindToRenderer();
        }
    }
}

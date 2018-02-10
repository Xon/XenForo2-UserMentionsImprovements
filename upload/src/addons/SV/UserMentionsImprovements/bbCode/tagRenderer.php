<?php


namespace SV\UserMentionsImprovements\bbCode;

use XF\BbCode\Renderer\Html;

class tagRenderer
{
    /**  @var Html */
    private $renderer;
    /** @var string */
    private $type;

    /**
     * tagRenderer constructor.
     *
     * @param Html   $renderer
     * @param string $type
     */
    public function __construct(Html $renderer, $type)
    {
        $this->renderer = $renderer;
        $this->type = $type;
    }

    public function bindToRenderer()
    {
        $callback = [$this, 'renderTagUserGroup'];
        // php 7.1+ only, but has better performance
        if (is_callable('\Closure::fromCallable'))
        {
            $callback = \Closure::fromCallable($callback);
        }

        $this->renderer->addTag(
            'usergroup',
            [
                'callback' => $callback
            ]
        );
    }

    public function renderTagUserGroup(
        /** @noinspection PhpUnusedParameterInspection */
        array $children,
        $option,
        array $tag,
        array $options,
        Html $renderer)
    {
        $content = $this->renderer->renderSubTree($children, $options);
        if ($content === '')
        {
            return '';
        }

        $userGroupId = intval($option);
        if ($userGroupId <= 0)
        {
            return $content;
        }

        $link = \XF::app()->router('public')->buildLink('full:members/usergroup', ['user_group_id' => $userGroupId]);
        $groupUsernameStyle = \XF::app()->options()->sv_styleGroupUsername
            ? 'username'
            : '';

        return $this->renderer->wrapHtml(
            '<a href="' . htmlspecialchars($link) . '" class="' . $groupUsernameStyle . ' ug" data-xf-init="sv-usergroup-tooltip" data-usergroup-id="' . $userGroupId . '" data-groupname="' . htmlspecialchars($content) . '">',
            $content,
            '</a>'
        );
    }
}

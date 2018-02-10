<?php


namespace SV\UserMentionsImprovements\bbCode;

use XF\BbCode\Renderer\Html;

class tagRenderer
{
    /**  @var Html */
    private $renderer;
    /** @var string */
    private $type;

    protected $styleGroupUsername     = false;
    protected $displayMiniGroupAvatar = false;
    protected $displayMiniUserAvatar  = false;

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
        $options = \XF::app()->options();
        $this->styleGroupUsername = \boolval($options->sv_styleGroupUsername);
        //$this->displayMiniGroupAvatar = \boolval($options->sv_displayUserAvatar);
        //$this->displayMiniUserAvatar = \boolval($options->sv_displayGroupAvatar);
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
        $displayMiniGroupAvatar = $this->displayMiniGroupAvatar ? 'avatar' : '';
        $groupUsernameStyle = $this->styleGroupUsername ? 'username' : '';

        $link = htmlspecialchars($link);
        $content = htmlspecialchars($content);

        return $this->renderer->wrapHtml(
            "<a href='{$link}' class='ug {$groupUsernameStyle} {$displayMiniGroupAvatar}' data-xf-init='sv-usergroup-tooltip' data-usergroup-id='{$userGroupId}' data-groupname='{$content}' >",
            $content,
            '</a>'
        );
    }
}

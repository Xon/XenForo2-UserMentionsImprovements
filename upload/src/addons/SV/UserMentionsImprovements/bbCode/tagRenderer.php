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
        $this->renderer->addTag(
            'usergroup',
            [
                'callback' => \Closure::fromCallable([$this, 'renderTagUserGroup'])
            ]
        );
    }

    static $groupAvatar   = null;
    static $groupUsername = null;

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
        if (self::$groupAvatar === null)
        {
            $options = \XF::app()->options();
            self::$groupUsername = $options->sv_styleGroupUsername
                ? 'username'
                : '';
            self::$groupAvatar = $options->sv_displayGroupAvatar
                ? '<span class="groupImg"></span>'
                : '';
        }

        return $this->renderer->wrapHtml(
            '<a href="' . htmlspecialchars($link) . '" class="' . self::$groupUsername . ' ug" data-usergroup="' . $userGroupId . ', ' . htmlspecialchars($content) . '"><span class="style' . $userGroupId . '">' . self::$groupAvatar,
            $content,
            '</span></a>'
        );
    }
}

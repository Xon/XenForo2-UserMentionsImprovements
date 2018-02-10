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

    protected $groupAvatar   = null;
    protected $groupUsername = null;

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
        if ($this->groupAvatar === null)
        {
            $options = \XF::app()->options();
            $this->groupUsername = $options->sv_styleGroupUsername
                ? 'username'
                : '';
            $this->groupAvatar = $options->sv_displayGroupAvatar
                ? '<span class="groupImg"></span>'
                : '';
        }

        return $this->renderer->wrapHtml(
            '<a href="' . htmlspecialchars($link) . '" class="' . $this->groupUsername . ' ug" data-usergroup="' . $userGroupId . ', ' . htmlspecialchars($content) . '"><span class="style' . $userGroupId . '">' . $this->groupAvatar,
            $content,
            '</span></a>'
        );
    }
}

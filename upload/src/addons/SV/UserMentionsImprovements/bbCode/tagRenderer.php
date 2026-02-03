<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 * @noinspection PhpMissingParamTypeInspection
 */

namespace SV\UserMentionsImprovements\bbCode;

use SV\UserMentionsImprovements\XF\Entity\User as ExtendedUserEntity;
use XF\BbCode\Renderer\Html;
use function htmlspecialchars;

class tagRenderer
{
    /**  @var Html */
    protected $renderer;
    /** @var string */
    protected $type;
    /** @var bool */
    protected $canViewPublicGroups;

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

        /** @var ExtendedUserEntity $visitor */
        $visitor = \XF::visitor();
        $this->canViewPublicGroups = $visitor->canViewPublicGroupsUMI();
    }

    public function bindToRenderer(): void
    {
        $callback = [$this, 'renderTagUserGroup'];
        $callback = \Closure::fromCallable($callback);

        /** @noinspection SpellCheckingInspection */
        $this->renderer->addTag(
            'usergroup',
            [
                'callback' => $callback,
            ]
        );
    }

    /**
     * @param int    $groupId
     * @param string $css
     * @param string $link HTML escaped text
     * @param string $groupName HTML escaped text
     * @param string $title HTML escaped text
     * @return string
     */
    public static function renderTagUserGroupTemplate(int $groupId, string $css, string $link, string $groupName, string $title): string
    {
        return "<a href='{$link}' class='{$css}' data-xf-click='overlay' data-usergroup-id='{$groupId}' data-groupname='{$groupName}'>{$title}</a>";
    }

    /**
     * @deprecated
     */
    public function renderTagUserGroupHtml($userGroupId, $css, $link, $content)
    {
        return static::renderTagUserGroupTemplate($userGroupId, $css, $link, $content, $content);
    }

    public function renderTagUserGroup(
        /** @noinspection PhpUnusedParameterInspection */
        array $children,
              $option,
        array $tag,
        array $options,
        Html  $renderer)
    {
        $content = $this->renderer->renderSubTree($children, $options);
        if ($content === '')
        {
            return '';
        }

        if (!$this->canViewPublicGroups)
        {
            return $content;
        }

        $userGroupId = (int)$option;
        if ($userGroupId <= 0)
        {
            return $content;
        }

        /** @noinspection SpellCheckingInspection */
        $link = \XF::app()->router('public')->buildLink('full:members/usergroup', ['user_group_id' => $userGroupId]);
        $link = htmlspecialchars($link);
        $content = htmlspecialchars($content);

        // todo next major version; replace with a direct call to `static::renderTagUserGroupTemplate`
        /** @noinspection PhpDeprecationInspection */
        return $this->renderTagUserGroupHtml($userGroupId, 'ug', $link, $content);
    }
}

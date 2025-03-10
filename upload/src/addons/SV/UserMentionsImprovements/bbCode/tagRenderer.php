<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 * @noinspection PhpMissingParamTypeInspection
 */

namespace SV\UserMentionsImprovements\bbCode;

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

        $visitor = \XF::visitor();
        $this->canViewPublicGroups = (\XF::options()->svUMIPermDeniedOnViewGroup ?? true) ||
                                     $visitor->hasPermission('general', 'sv_ViewPrivateGroups') ||
                                     $visitor->hasPermission('general', 'sv_ViewPublicGroups');
    }

    public function bindToRenderer(): void
    {
        $callback = [$this, 'renderTagUserGroup'];
        $callback = \Closure::fromCallable($callback);

        $this->renderer->addTag(
            'usergroup',
            [
                'callback' => $callback,
            ]
        );
    }

    /**
     * @param int    $userGroupId
     * @param string $css
     * @param string $link    HTML escaped text
     * @param string $content HTML escaped text
     * @return string
     */
    public function renderTagUserGroupHtml($userGroupId, $css, $link, $content)
    {
        return $this->renderer->wrapHtml(
            "<a href='$link' class='$css' data-xf-click='overlay' data-usergroup-id='$userGroupId' data-groupname='$content' >",
            $content,
            '</a>');
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

        if (!$this->canViewPublicGroups)
        {
            return $content;
        }

        $userGroupId = (int)$option;
        if ($userGroupId <= 0)
        {
            return $content;
        }

        $link = \XF::app()->router('public')->buildLink('full:members/usergroup', ['user_group_id' => $userGroupId]);
        $link = htmlspecialchars($link);
        $content = htmlspecialchars($content);

        return $this->renderTagUserGroupHtml($userGroupId, 'ug', $link, $content);
    }
}

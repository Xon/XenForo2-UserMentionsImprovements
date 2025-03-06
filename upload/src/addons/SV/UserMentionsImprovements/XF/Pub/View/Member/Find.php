<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\UserMentionsImprovements\XF\Pub\View\Member;

use SV\UserMentionsImprovements\XF\Entity\UserGroup as UserGroupEntity;

/**
 * @extends \XF\Pub\View\Member\Find
 */
class Find extends XFCP_Find
{
    public function renderJson()
    {
        $response = parent::renderJson();

        if (isset($this->params['userGroups']))
        {
            foreach ($this->params['userGroups'] as $usergroup)
            {
                /** @var UserGroupEntity $usergroup */
                \array_unshift(
                    $response['results'],
                    [
                        'id'       => $usergroup->user_group_id,
                        'iconHtml' => $usergroup->icon_html,
                        'text'     => $usergroup->title,
                        'q'        => $this->params['q'],
                    ]
                );
            }
        }

        return $response;
    }
}

<?php

namespace SV\UserMentionsImprovements\XF\Pub\View\Member;

class Find extends XFCP_Find
{
    public function renderJson()
    {
        $response = parent::renderJson();

        if (isset($this->params['userGroups']))
        {
            foreach ($this->params['userGroups'] as $usergroup)
            {
                /** @var \SV\UserMentionsImprovements\XF\Entity\UserGroup $usergroup */
                array_unshift(
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

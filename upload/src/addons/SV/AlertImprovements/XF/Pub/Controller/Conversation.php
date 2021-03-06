<?php

namespace SV\AlertImprovements\XF\Pub\Controller;

use SV\AlertImprovements\XF\Repository\UserAlert;
use XF\Entity\ConversationUser;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

class Conversation extends XFCP_Conversation
{
    public function actionView(ParameterBag $params)
    {
        $reply = parent::actionView($params);

        if ($reply instanceof View && !empty($messages = $reply->getParam('messages')))
        {
            $visitor = \XF::visitor();

            if ($visitor->user_id && $visitor->alerts_unread)
            {
                $contentIds  = $messages->keys();
                $contentType = 'conversation_message';

                /** @var UserAlert $alertRepo */
                $alertRepo = $this->repository('XF:UserAlert');
                $alertRepo->markAlertsReadForContentIds($contentType, $contentIds);
            }
        }

        return $reply;
    }

    /**
     * @param ConversationUser $convUser
     * @param int          $lastDate
     * @param int          $limit
     * @return array
     */
    protected function _getNextLivePosts(ConversationUser $convUser, $lastDate, $limit = 3)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /**
         * @var AbstractCollection $contents
         * @var int $lastDate
         */
        list ($contents, $lastDate) = parent::_getNextLivePosts($convUser, $lastDate, $limit);

        $contentIds  = $contents->keys();
        $contentType = 'conversation_message';

        /** @var UserAlert $alertRepo */
        $alertRepo = $this->repository('XF:UserAlert');
        $alertRepo->markAlertsReadForContentIds($contentType, $contentIds);

        return [$contents, $lastDate];
    }
}

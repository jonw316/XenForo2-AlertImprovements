<?php


namespace SV\AlertImprovements\XF\Entity;

use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Structure;

/**
 * Class UserAlert
 *
 * @property bool IsSummary
 * @property int summerize_id
 * @property UserAlert SummaryAlert
 * @property \SV\ContentRatings\Entity\RatingType[]|AbstractCollection sv_rating_types
 */
class UserAlert extends XFCP_UserAlert
{
    public function getIsSummary()
    {
        if ($this->summerize_id === null)
        {
            return (bool)preg_match('/^.*_summary$/', $this->action);
        }

        return false;
    }

    public function getSvRatingTypes()
    {
        if (isset($this->extra_data['rating_type_id']) && is_array($this->extra_data['rating_type_id']))
        {
            $ratings = $this->extra_data['rating_type_id'];
            /** @var \SV\ContentRatings\Repository\RatingType $ratingTypeRepo */
            $ratingTypeRepo = $this->repository('SV\ContentRatings:RatingType');
            $ratingTypes = $ratingTypeRepo->getRatingTypesAsEntities();

            return $ratingTypes->filter(function ($item) use ($ratings) {
                /** @var \SV\ContentRatings\Entity\RatingType $item */
                return isset($ratings[$item->rating_type_id]);
            });
        }

        return null;
    }

    public function getLikedContentSummary($glue = ' ')
    {
        $extra = $this->extra_data;
        if (isset($extra['ct']) && is_array($extra['ct']))
        {
            $phrases = [];
            foreach ($extra['ct'] as $contentType => $count)
            {
                if ($count)
                {
                    $contentTypePhrase = \XF::app()->getContentTypePhrase($contentType, $count > 1);
                    if ($contentTypePhrase)
                    {
                        $phrases[] = \XF::phraseDeferred("sv_x_of_y_content_type", ['count' => $count, 'contentType' => \utf8_strtolower($contentTypePhrase)]);
                    }
                }
            }

            if ($phrases)
            {
                return join($glue, $phrases);
            }
        }

        return '';
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['summerize_id'] = ['type' => self::UINT, 'nullable' => true, 'default' => null];

        $structure->getters['isSummary'] = [
            'getter' => true,
            'cache'  => true
        ];
        $structure->getters['sv_rating_types'] = true;

        $structure->relations['SummaryAlert'] = [
            'entity'     => 'XF:UserAlert',
            'type'       => self::TO_ONE,
            'conditions' => [['alert_id', '=', '$summerize_id']],
            'primary'    => true
        ];

        return $structure;
    }
}

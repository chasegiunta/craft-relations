<?php

/**
 * Relations plugin for Craft CMS 3
 *
 * A field type to show reverse related elements
 *
 * @link      https://naveedziarab.co.uk/
 * @copyright Copyright (c) 2018 Nav33d
 */

namespace nav33d\relations\services;

use Craft;
use craft\db\Query;

use yii\base\Component;

use nav33d\relations\Relations as RelationsPlugin;

class Relations extends Component
{

    public function get($element, $targetTypes, $targetFields)
    {
        $relatedElements = [];

        if ( !$element )
        {
            return [];
        }

        // If no target types are selected
        if ($targetFields != '*' && !is_array($targetTypes) ) {
            return [];
        }

        $where = [];
        $where['targetId'] = $element->id;
        
        if ($targetFields != '*') {
            $where['fieldId'] = $targetFields;
        }
        

        $relatedIds = (new Query())
            ->select(['sourceId'])
            ->from(['{{%relations}}'])
            ->where($where)
            ->column();

        if ( !$relatedIds )
        {
            return [];
        }

        $elementsService = Craft::$app->elements;
        foreach ( $relatedIds as $id )
        {
            $relatedElement = $elementsService->getElementById($id);
            
            if( !$relatedElement )
            {
                continue;
            }

            if($targetTypes != '*') {
                if (!in_array($relatedElement->displayName(), $targetTypes)) {
                    continue;
                }
            }
            
            if ( method_exists($relatedElement, 'getOwner') && $relatedElement->getOwner() )
            {
                while ( method_exists($relatedElement, 'getOwner') )
                {
                    $relatedElement = $relatedElement->getOwner();
                }
            }

            if ( !isset($relatedElements[$relatedElement->id]) )
            {
                $relatedElements[$relatedElement->id] = $relatedElement;
            }
        }

        return $relatedElements;
    }

}

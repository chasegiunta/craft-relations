<?php

/**
 * Relations plugin for Craft CMS 3
 *
 * A field type to show reverse related elements
 *
 * @link      https://naveedziarab.co.uk/
 * @copyright Copyright (c) 2018 Nav33d
 */

namespace nav33d\relations\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;
use craft\helpers\Template;
use craft\fields\Entries;
use craft\fields\Assets;
use craft\fields\Users;
use craft\fields\Categories;
use craft\fields\Tags;

use nav33d\relations\Relations as RelationsPlugin;
use nav33d\relations\assetbundles\RelationsAsset;

class Relations extends Field
{
    /**
     * @var mixed Target field setting
     */
    public $targetFields = '*';
    public $targetTypes = '*';


    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this field type.
     *
     * @return string The display name of this field type.
     */
    public static function displayName(): string
    {
        return Craft::t('relations', 'Relations');
    }


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return RelationsPlugin::$plugin->relations->get($element, $this->targetTypes, $this->targetFields);
    }

    /**
     * {@inheritdoc}
     */
    public function settingsAttributes(): array
    {
        $attributes = parent::settingsAttributes();
        $attributes[] = 'targetFields';
        $attributes[] = 'targetTypes';
        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsHtml()
    {
        // Get available fields
        $fields = [];
        /** @var Field $field */
        foreach (Craft::$app->fields->getAllFields() as $field) {
            if (
                $field instanceof Entries       ||
                $field instanceof Assets        ||
                $field instanceof Users         ||
                $field instanceof Categories    ||
                $field instanceof Tags
            ) {
                $fields[$field->id] = "$field->name";
            }
        }

        $types = [
            'Entry' => 'Entry',
            'Asset' => 'Asset',
            'User' => 'User',
            'Category' => 'Category',
            'Tag' => 'Tag'
        ];

        // Add "field" select template
        return $fieldSelectTemplate = Craft::$app->view->renderTemplate(
            'relations/_settings',
            [
                'fields' => $fields,
                'types' => $types,
                'settings' => $this->getSettings(),
            ]
        );
    }


    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();

        // Register our asset bundle
        $view->registerAssetBundle(RelationsAsset::class);

        // Get reverse related elements
        $relations = $value;
        if ( !$relations )
        {
            $relations = RelationsPlugin::$plugin->relations->get($element, $this->targetTypes, $this->targetFields);
        }

        $showType = true;
        if ($this->targetTypes != '*' && count($this->targetTypes) == 1) {
            $showType = false;
        }

        return $view->renderTemplate('relations/fields/relations/_input', [
            'relations' => $relations,
            'showType' => $showType
        ]);
    }


    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return null;
    }


}

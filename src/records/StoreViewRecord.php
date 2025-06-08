<?php

namespace nelsonnguyen\craftstoreview\records;

use Craft;
use craft\db\ActiveQuery;
use craft\db\ActiveRecord;
use craft\elements\Entry;
use craft\records\Element;
use craft\records\Element_SiteSettings;
use craft\records\Site;

use yii\db\ActiveQueryInterface;

/**
 * @property int $id
 * @property int $elementId
 * @property int $siteId
 * @property string $uri
 * @property int $total
 * @property int $day
 * @property int $week
 * @property int $month
 * @property \DateTime $lastUpdated
 */
class StoreViewRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%storeview}}';
    }

    /**
     * Returns the element.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'elementId']);
    }

    /**
     * Returns the associated site.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getSite(): ActiveQueryInterface
    {
        return $this->hasOne(Site::class, ['id' => 'siteId']);
    }

    /**
     * Returns the element info.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElementInfo(): ActiveQueryInterface
    {
        return $this->hasOne(Element_SiteSettings::class, ['elementId' => 'elementId', 'siteId' => 'siteId']);
    }
}

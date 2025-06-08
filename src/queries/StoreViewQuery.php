<?php

namespace nelsonnguyen\craftstoreview\queries;

use Craft;
use craft\db\Table;
use nelsonnguyen\craftstoreview\models\StoreViewModel;
use nelsonnguyen\craftstoreview\records\StoreViewRecord;
use nelsonnguyen\craftstoreview\StoreView;
use yii\db\Expression;
use yii\db\Query;

class StoreViewQuery
{
    private Query $query;
    private bool $isCraft5;

    public function __construct()
    {
        $this->isCraft5 = StoreView::$plugin->storeView->isCraft5();

        if ($this->isCraft5) {
            $elementQuery = (new Query())->select([
                'e.id AS elementIdMain',
                'e.type AS elementTypeMain',
                'es.title AS elementTitleMain',
                'es.siteId AS elementSiteIdMain',
                'es.slug AS elementSlugMain',
                'es.uri AS elementUriMain',
            ])
                ->from(['e' => Table::ELEMENTS])
                ->leftJoin(['es' => Table::ELEMENTS_SITES], '[[e.id]] = [[es.elementId]]');
        } else {
            $elementQuery = (new Query())
                ->select([
                    'e.id AS elementIdMain',
                    'c.title AS elementTitleMain',
                    'e.type AS elementTypeMain',
                    'es.siteId AS elementSiteIdMain',
                    'es.slug AS elementSlugMain',
                    'es.uri AS elementUriMain',
                ])
                ->from(['e' => Table::ELEMENTS])
                ->leftJoin(['es' => Table::ELEMENTS_SITES], '[[e.id]] = [[es.elementId]]')
                ->leftJoin(['c' => '{{%content}}'], '[[c.elementId]] = [[e.id]] AND [[c.siteId]] = [[es.siteId]]');
        }

        $this->query = (new Query())
            ->select([
                'id',
                'uri',
                'elementId',
                'siteId',
                'total',
                'day',
                'week',
                'month',
                'lastUpdated',
                'eMain.elementTitleMain AS elementTitle',
                'eMain.elementTypeMain AS type',
                'eMain.elementUriMain AS elementUri',
                'eMain.elementSlugMain AS elementSlug',
            ])
            ->from(['s' => StoreViewRecord::tableName()])
            ->leftJoin(
                ['eMain' => $elementQuery],
                '[[eMain.elementIdMain]] = [[s.elementId]] AND [[eMain.elementSiteIdMain]] = [[s.siteId]]'
            );


        $primarySiteId = Craft::$app->getSites()->getPrimarySite()->id ?? null;

        if ($primarySiteId !== null) {
            $this->query->andWhere(['siteId' => $primarySiteId]);
        }
    }

    private static function formatData($item)
    {
        if ($item == null) return;
        return StoreViewModel::fromRaw($item);
    }

    private function containsSiteIdCondition(array $condition): bool
    {
        // Simple recursive check if any 'siteId' key exists anywhere in the condition array
        foreach ($condition as $key => $value) {
            if ($key === 'siteId') {
                return true;
            }
            if (is_array($value) && $this->containsSiteIdCondition($value)) {
                return true;
            }
        }
        return false;
    }

    private function removeSiteIdCondition(): void
    {
        $where = $this->query->where;

        if (is_array($where)) {
            $this->query->where = $this->removeSiteIdFromCondition($where);
        }
    }

    private function removeSiteIdFromCondition(array $condition): array
    {
        // Recursively remove all 'siteId' conditions from the where array
        foreach ($condition as $key => $value) {
            if ($key === 'siteId') {
                unset($condition[$key]);
            } elseif (is_array($value)) {
                $condition[$key] = $this->removeSiteIdFromCondition($value);
            }
        }
        return $condition;
    }

    public function where(array $condition): static
    {
        if ($this->containsSiteIdCondition($condition)) {
            // Remove default siteId condition before applying new condition with siteId
            $this->removeSiteIdCondition();
        }
        $this->query->andWhere($condition);
        return $this;
    }



    public function limit(int $limit): static
    {
        $this->query->limit($limit);
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->query->offset($offset);
        return $this;
    }

    public function orderBy(array|string $columns): static
    {
        $this->query->orderBy($columns);
        return $this;
    }

    public function all(): array
    {

        $rows = $this->query->all();
        $res = array_map([self::class, 'formatData'], $rows);
        return $res;
    }

    public function one()
    {
        return $this->formatData($this->query->one());
    }

    public function count(): int
    {
        return (int) $this->query->count();
    }

    public function withRange(string $range = 'all'): static
    {
        switch ($range) {
            case 'today':
                $this->query->andWhere(new Expression('[[lastUpdated]] >= CURDATE() AND [[lastUpdated]] < CURDATE() + INTERVAL 1 DAY'));
                break;
            case 'thisWeek':
                $this->query->andWhere(new Expression('[[lastUpdated]] >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND [[lastUpdated]] < CURDATE() + INTERVAL 1 DAY'));
                break;
            case 'thisMonth':
                $this->query->andWhere(new Expression('[[lastUpdated]] >= DATE_SUB(CURDATE(), INTERVAL DAY(CURDATE()) - 1 DAY) AND [[lastUpdated]] < CURDATE() + INTERVAL 1 DAY'));
                break;
        }
        return $this;
    }

    public function sections(array|string $value): static
    {

        $query = (new Query())
            ->select([
                'entries.id AS entriesId',
                'entries.sectionId AS sectionId',
                'sections.handle AS sectionHandle',
            ])
            ->from(['entries' => Table::ENTRIES])
            ->innerJoin(
                ['sections' => Table::SECTIONS],
                '[[sectionId]] = [[sections.id]]'
            );

        $this->query->addSelect([
            'sectionId',
            'sectionHandle'
        ]);
        $this->query->leftJoin(
            ['s' => $query],
            '[[elementId]] = [[s.entriesId]]'
        );
        if (is_string($value)) {
            $this->query->andWhere(['sectionHandle' => $value]);
        } else if (is_array($value)) {
            $this->query->andWhere(['in', 'sectionHandle', $value]);
        }
        return $this;
    }

    public function categories(array|string $value): static
    {

        $query = (new Query())
            ->select([
                'categories.id AS categoriesId',
                'categories.groupId AS categoriesGroupId',
                'categorygroups.handle AS categorygroupsHandle',
            ])
            ->from(['categories' => Table::CATEGORIES])
            ->innerJoin(
                ['categorygroups' => Table::CATEGORYGROUPS],
                '[[categories.groupId]] = [[categorygroups.id]]'
            );


        $this->query->addSelect([
            'categoriesGroupId',
            'categorygroupsHandle'
        ]);
        $this->query->leftJoin(
            ['c' => $query],
            '[[elementId]] = [[c.categoriesId]]'
        );
        if (is_string($value)) {
            $this->query->andWhere(['categorygroupsHandle' => $value]);
        } else if (is_array($value)) {
            $this->query->andWhere(['in', 'categorygroupsHandle', $value]);
        }
        return $this;
    }

    public function tags(array|string $value): static
    {

        $query = (new Query())
            ->select([
                'tags.id AS tagsId',
                'tags.groupId AS tagsGroupId',
                'taggroups.handle AS taggroupsHandle',
            ])
            ->from(['tags' => Table::TAGS])
            ->innerJoin(
                ['taggroups' => Table::TAGGROUPS],
                '[[tags.groupId]] = [[taggroups.id]]'
            );

        $this->query->addSelect([
            'tagsGroupId',
            'taggroupsHandle'
        ]);
        $this->query->leftJoin(
            ['t' => $query],
            '[[elementId]] = [[t.tagsId]]'
        );
        if (is_string($value)) {
            $this->query->andWhere(['taggroupsHandle' => $value]);
        } else if (is_array($value)) {
            $this->query->andWhere(['in', 'taggroupsHandle', $value]);
        }
        return $this;
    }
}

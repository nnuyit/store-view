<?php

namespace nelsonnguyen\craftstoreview\models;

use Craft;
use craft\base\Model;

class StoreViewElementModel extends Model
{
    public int $id;
    public string $title;
    public ?string $slug;
    public ?string $uri;
    public ?string $type;
    public ?string $cpEditUrl = null;
    public ?int $sectionId;
    public ?string $sectionHandle;
    public ?int $categoryGroupId;
    public ?string $categoryGroupHandle;
    public ?int $tagGroupId;
    public ?string $tagGroupHandle;
    public static function fromRaw(mixed $raw): self
    {
        $model = new self();
        $model->id = $raw['elementId'];
        $model->title = $raw['elementTitle'];
        $model->slug = $raw['elementSlug'];
        $model->uri = $raw['elementUri'];
        $model->type = class_exists($raw['type']) ? (new \ReflectionClass($raw['type']))->getShortName() : $raw['type'];

        if (isset($raw['sectionId'])) {
            $model->sectionId = $raw['sectionId'];
        }
        if (isset($raw['sectionHandle'])) {
            $model->sectionHandle = $raw['sectionHandle'];
        }

        if (isset($raw['categoriesGroupId'])) {
            $model->categoryGroupId = $raw['categoriesGroupId'];
        }
        if (isset($raw['categorygroupsHandle'])) {
            $model->categoryGroupHandle = $raw['categorygroupsHandle'];
        }

        if (isset($raw['tagsGroupId'])) {
            $model->tagGroupId = $raw['tagsGroupId'];
        }
        if (isset($raw['taggroupsHandle'])) {
            $model->tagGroupHandle = $raw['taggroupsHandle'];
        }

        if (Craft::$app->request->getIsCpRequest()) {
            if (Craft::$app->getUser()->getIsAdmin()) {
                $model->cpEditUrl = Craft::$app->getElements()->getElementById(
                    $raw['elementId'],
                    $raw['type'],
                    $raw['siteId']
                )->getCpEditUrl();
            }
        }
        return $model;
    }
}

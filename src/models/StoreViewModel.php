<?php

namespace nelsonnguyen\craftstoreview\models;

use craft\base\Model;

class StoreViewModel extends Model
{
    public int $id;
    public ?string $uri = null;
    public ?int $elementId = null;
    public int $siteId;
    public int $total = 0;
    public int $day = 0;
    public int $week = 0;
    public int $month = 0;
    public \DateTimeImmutable|null $lastUpdated = null;
    public StoreViewElementModel|null $element = null;

    public static function fromRaw(mixed $raw): self
    {
        $model = new self();
        $model->id = $raw['id'];
        $model->elementId = $raw['elementId'] ?: null;
        $model->uri = $raw['uri'] ?: null;
        $model->siteId = $raw['siteId'];
        $model->total = $raw['total'];
        $model->day = $raw['day'];
        $model->week = $raw['week'];
        $model->month = $raw['month'];
        $model->lastUpdated = new \DateTimeImmutable($raw['lastUpdated'], new \DateTimeZone('UTC'));
        if ($raw['elementId'] != null) {
            $model->element = StoreViewElementModel::fromRaw($raw);
        }

        return $model;
    }
}

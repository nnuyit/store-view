<?php

namespace nelsonnguyen\craftstoreview\services;

use Craft;
use craft\base\Component;
use craft\helpers\Db;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use nelsonnguyen\craftstoreview\queries\StoreViewQuery;
use nelsonnguyen\craftstoreview\records\StoreViewRecord;
use nelsonnguyen\craftstoreview\StoreView;

class StoreViewService  extends Component
{
    public function countView(array $condition): void
    {

        if ($this->ignoreIp()) return;
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $currentDate = $now->format('Y-m-d');
        $currentWeek = (int)$now->format('W');
        $currentMonth = (int)$now->format('n');
        $currentYear = (int)$now->format('Y');

        // Get record from DB
        $storeViewRecord = StoreViewRecord::find()
            ->where($condition)
            ->one();

        if (!$storeViewRecord) {
            $storeViewRecord = new StoreViewRecord();
            // Set identifying fields from the $condition array
            foreach ($condition as $key => $value) {
                $storeViewRecord->{$key} = $value;
            }
            $storeViewRecord->total = 1;
            $storeViewRecord->day = 1;
            $storeViewRecord->week = 1;
            $storeViewRecord->month = 1;
        } else {
            // Existing record: check if we need to reset any counters
            $lastUpdated = new \DateTimeImmutable($storeViewRecord->lastUpdated);
            $needsDayReset = $lastUpdated->format('Y-m-d') !== $currentDate;
            $needsWeekReset = $lastUpdated->format('W-Y') !== "$currentWeek-$currentYear";
            $needsMonthReset = $lastUpdated->format('n-Y') !== "$currentMonth-$currentYear";

            $storeViewRecord->day = $needsDayReset ? 1 : $storeViewRecord->day + 1;
            $storeViewRecord->week = $needsWeekReset ? 1 : $storeViewRecord->week + 1;
            $storeViewRecord->month = $needsMonthReset ? 1 : $storeViewRecord->month + 1;
            $storeViewRecord->total++;
        }
        $storeViewRecord->lastUpdated = Db::prepareDateForDb($now);
        $storeViewRecord->save();
    }

    public function getEntries()
    {
        return new StoreViewQuery();
    }

    public function isBot()
    {
        $CrawlerDetect = new CrawlerDetect;
        if ($CrawlerDetect->isCrawler()) {
            return true;
        }
        return false;
    }

    public function ignoreIp(): bool
    {
        $settings = StoreView::$plugin->settings;
        $userIp = Craft::$app->getRequest()->getUserIP();
        $ignoreIps = explode("\n", $settings->ignoreIps);
        if ($settings->ignoreIps && in_array($userIp, $ignoreIps, true)) {
            return true;
        }
        return false;
    }

    public function isCraft5(): bool
    {
        return version_compare(Craft::$app->getVersion(), '5.0.0', '>=');
    }
}

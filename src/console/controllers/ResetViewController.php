<?php

namespace nelsonnguyen\craftstoreview\console\controllers;

use Craft;
use craft\console\Controller;
use craft\helpers\Console;
use nelsonnguyen\craftstoreview\records\StoreViewRecord;
use yii\console\ExitCode;
use yii\log\Logger;

/**
 * Reset View controller
 */
class ResetViewController extends Controller
{
    public $defaultAction = 'index';

    // public function options($actionID): array
    // {
    //     $options = parent::options($actionID);
    //     switch ($actionID) {
    //         case 'index':
    //             // $options[] = '...';
    //             break;
    //     }
    //     return $options;
    // }

    /**
     * store-view/reset-view command
     */
    public function actionIndex(): int
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $today = $now->format('Y-m-d');
        $startOfWeek = $now->modify('monday this week')->format('Y-m-d');
        $startOfMonth = $now->modify('first day of this month')->format('Y-m-d');
        $db = Craft::$app->getDb();
        // from start week to today.
        $idsNotToday = $db->createCommand("
            SELECT id
            FROM {{%storeview}}
            WHERE DATE(lastUpdated) >= :startOfWeek
            AND DATE(lastUpdated) < :today
            AND day > 0
        ")->bindValues([
            ':startOfWeek' => $startOfWeek,
            ':today' => $today,
        ])->queryColumn();

        if (!empty($idsNotToday)) {
            $this->outputLog("Reset day count in ids: " . join(", ", $idsNotToday));
            $db->createCommand()->update(
                '{{%storeview}}',
                [
                    'day' => 0,
                ],
                ['in', 'id', $idsNotToday]
            )->execute();
        } else {
            $this->outputLog("Reset day count not found");
        }

        // from start month to this week.
        $idsNotThisWeek = $db->createCommand("
            SELECT id
            FROM {{%storeview}}
            WHERE DATE(lastUpdated) >= :startOfMonth
            AND DATE(lastUpdated) < :startOfWeek
            AND week > 0
        ")->bindValues([
            ':startOfMonth' => $startOfMonth,
            ':startOfWeek' => $startOfWeek,
        ])->queryColumn();

        if (!empty($idsNotThisWeek)) {
            $this->outputLog("Reset week count in ids: " . join(", ", $idsNotThisWeek));
            $db->createCommand()->update(
                '{{%storeview}}',
                [
                    'week' => 0,
                ],
                ['in', 'id', $idsNotThisWeek]
            )->execute();
        } else {
            $this->outputLog("Reset week count not found");
        }

        // from start year to this month.
        $idsNotThisMonth = $db->createCommand("
            SELECT id
            FROM {{%storeview}}
            WHERE DATE(lastUpdated) < :startOfMonth
            AND month > 0
        ")->bindValues([
            ':startOfMonth' => $startOfMonth,
        ])->queryColumn();

        if (!empty($idsNotThisMonth)) {
            $this->outputLog("Reset month count in ids: " . join(", ", $idsNotThisMonth));
            $db->createCommand()->update(
                '{{%storeview}}',
                [
                    'month' => 0,
                ],
                ['in', 'id', $idsNotThisMonth]
            )->execute();
        } else {
            $this->outputLog("Reset month count not found");
        }

        $this->stdout("Reset count completed.\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    private function outputLog(string $value)
    {
        Craft::getLogger()->log($value, Logger::LEVEL_INFO, 'store-view');
        $this->stdout("$value\n", Console::FG_GREEN);
    }
}

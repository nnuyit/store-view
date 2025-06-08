<?php

namespace nelsonnguyen\craftstoreview;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use nelsonnguyen\craftstoreview\models\StoreViewSettingsModel;
use nelsonnguyen\craftstoreview\services\StoreViewService;
use nelsonnguyen\craftstoreview\variables\StoreViewVariable;
use yii\base\Event;

/**
 * Store View plugin
 *
 * @method static StoreView getInstance()
 * @author Nelson Nguyen <nnuyit@gmail.com>
 * @copyright Nelson Nguyen
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read StoreViewService $storeView
 */

class StoreView extends Plugin
{
    /**
     * @var StoreView
     */
    public static StoreView $plugin;
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    public static function config(): array
    {
        return [
            'components' => [
                'storeView' => ['class' => StoreViewService::class],
            ],
        ];
    }

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        // Register variable
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('storeView', StoreViewVariable::class);
        });

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['store-view'] = 'store-view/store-view';
                $event->rules['store-view/delete/<id:\d+>'] = 'store-view/store-view/delete';
                $event->rules['store-view/reset/<id:\d+>'] = 'store-view/store-view/reset';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (Event $event) {
                // Add a custom frontend route like '/api/track-page-view' that maps to your controller action
                $event->rules['store-view/api/track-page-view'] = 'store-view/store-view-frontend/track-view';
            }
        );
    }

    protected function createSettingsModel(): StoreViewSettingsModel
    {
        return new StoreViewSettingsModel();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'store-view/setting',
            ['settings' => $this->getSettings()]
        );
    }
}

<?php

namespace nelsonnguyen\craftstoreview\variables;

use Craft;
use craft\web\View;
use nelsonnguyen\craftstoreview\assetbundles\StoreViewFrontendBundle;
use nelsonnguyen\craftstoreview\StoreView;

class StoreViewVariable
{
    public function getEntries()
    {
        return StoreView::$plugin->storeView->getEntries();
    }

    public function count(int|string|null $elementId = null): void
    {

        $reqSiteId = Craft::$app->getSites()->getCurrentSite()->id;
        $reqPath = Craft::$app->getRequest()->getPathInfo();

        if (Craft::$app->getRequest()->getIsPreview() || StoreView::$plugin->storeView->isBot() && Craft::$app->getRequest()->getIsAjax()) {
            return;
        }
        if (is_int($elementId) && !empty($elementId)) {
            StoreView::$plugin->storeView->countView([
                'elementId' => $elementId,
                'siteId' => $reqSiteId,
            ]);
        } else {
            StoreView::$plugin->storeView->countView([
                'uri' => $reqPath,
                'siteId' => $reqSiteId,
            ]);
        }
    }

    public function isCraft5()
    {
        return StoreView::$plugin->storeView->isCraft5();
    }
}

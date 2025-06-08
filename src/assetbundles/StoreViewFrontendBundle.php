<?php

namespace nelsonnguyen\craftstoreview\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class StoreViewFrontendBundle extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@nelsonnguyen/craftstoreview/resources';



        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'frontend.js',
        ];
        parent::init();
    }
}

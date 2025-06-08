<?php

namespace nelsonnguyen\craftstoreview\controllers;

use Craft;
use craft\helpers\ElementHelper;
use craft\web\Controller;
use Exception;
use nelsonnguyen\craftstoreview\StoreView;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Store View controller
 */
class StoreViewFrontendController extends Controller
{
    // Allow anonymous access
    protected array|int|bool $allowAnonymous = ['track-view'];


    public function actionTrackView()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $elementId = $request->getBodyParam('elementId');
        $uri = ltrim($request->getBodyParam('uri'), '/');
        $siteId = (int)$request->getBodyParam('siteId');

        $result = [
            'success' => false,
            'message' => ''
        ];
        if ($request->getQueryParam('x-craft-live-preview')) return;

        try {
            if (!$siteId || !Craft::$app->sites->getSiteById($siteId)) {
                throw new Exception('Missing or invalid "siteId"');
            }

            if (!$uri || !is_string($uri)) {
                throw new Exception('Missing or invalid "uri"');
            }
            $element = null;
            if ($elementId) {
                $element = Craft::$app->elements->getElementById((int)$elementId, null, $siteId);
                if (empty($element)) {
                    throw new Exception('Missing or invalid "elementId"');
                } else if (ElementHelper::isDraftOrRevision($element)) {
                    return;
                }
            }


            if (!empty($element)) {
                StoreView::$plugin->storeView->countView([
                    'elementId' => $elementId,
                    'siteId' => $siteId,
                ]);
            } else {
                StoreView::$plugin->storeView->countView([
                    'uri' => $uri,
                    'siteId' => $siteId,
                ]);
            }
            $result['success'] = true;
        } catch (\Exception $e) {
            Craft::$app->getResponse()->setStatusCode(400);
            $result['message'] = $e->getMessage();
        }
        return $this->asJson($result);
    }
}

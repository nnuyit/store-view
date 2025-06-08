<?php

namespace nelsonnguyen\craftstoreview\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use nelsonnguyen\craftstoreview\assetbundles\StoreViewBundle;
use nelsonnguyen\craftstoreview\queries\StoreViewQuery;
use nelsonnguyen\craftstoreview\records\StoreViewRecord;
use nelsonnguyen\craftstoreview\StoreView;
use yii\web\Response;

/**
 * Store View controller
 */
class StoreViewController extends Controller
{
    public $defaultAction = 'index';

    /**
     * store-view/store-view action
     */
    public function actionIndex(): Response
    {
        $request = Craft::$app->getRequest();

        $siteParam = $request->getParam('site');
        $site = $siteParam
            ? Craft::$app->getSites()->getSiteByHandle($siteParam)
            : Craft::$app->getSites()->getCurrentSite();

        if (!$site) {
            throw new \yii\web\NotFoundHttpException("Site not found");
        }

        $page = (int)$request->getParam('page', 1);
        $perPage = 10;
        $sort = $request->getParam('sort', 'lastUpdated');
        $range = $request->getParam('range', 'all');
        $order = strtoupper($request->getParam('order', 'desc'));
        $query = StoreView::$plugin->storeView->getEntries()
            ->where([
                'siteId' => $site->id
            ])->withRange($range);


        $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy([
                $sort => $order === 'ASC' ? SORT_ASC : SORT_DESC
            ]);

        $total = $query->count();

        $rows = $query->all();

        if (empty($rows) && $page > 1) {
            // Preserve query params
            $queryParams = $request->getQueryParams();
            $queryParams['page'] = 1;
            $queryParams['range'] = 'all';

            $url = UrlHelper::cpUrl('store-view', $queryParams);
            return $this->redirect($url);
        }
        $this->view->registerAssetBundle(StoreViewBundle::class);

        // echo "<pre>";
        // var_dump($siteParam);
        // echo "</pre>";
        // die('123123');
        return $this->renderTemplate('store-view/index', [
            'rows' => $rows,
            'pagination' => [
                'total' => $total,
                'perPage' => $perPage,
                'currentPage' => $page,
            ],
            'site' =>  $site->handle,
            'sort' => $sort,
            'order' => strtolower($order),
            'range' => $range
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $this->requireCpRequest(); // Only allow requests from Control Panel
        $this->requireAdmin();     // Only allow admin users
        $record = StoreViewRecord::findOne($id);

        if (!$record) {
            throw new \yii\web\NotFoundHttpException("Store view record not found.");
        }
        $record->delete();

        Craft::$app->getSession()->setNotice('Record deleted.');

        return $this->redirectToPostedUrl();
    }

    public function actionReset(int $id): Response
    {
        $this->requireCpRequest(); // Only allow requests from Control Panel
        $this->requireAdmin();     // Only allow admin users
        $record = StoreViewRecord::findOne($id);

        if (!$record) {
            throw new \yii\web\NotFoundHttpException("Store view record not found.");
        }
        $record->total = 0;
        $record->day = 0;
        $record->week = 0;
        $record->month = 0;
        $record->save();
        // $record->delete();

        Craft::$app->getSession()->setNotice('Record reset.');

        return $this->redirectToPostedUrl();
    }
}

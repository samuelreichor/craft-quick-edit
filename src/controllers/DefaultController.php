<?php

namespace samuelreichor\quickedit\controllers;

use Craft;
use craft\commerce\elements\Product;
use craft\elements\Entry;
use craft\web\Controller;
use samuelreichor\quickedit\helpers\Utils;
use samuelreichor\quickedit\QuickEdit;
use yii\base\Response;

class DefaultController extends Controller
{
    protected array|bool|int $allowAnonymous = true;

    /**
     * Get all information needed to render quick edit
     *
     * @return Response
     */
    public function actionGetQuickEdit(): Response
    {
        $editService = QuickEdit::getInstance()->edit;

        if (!$editService->canRender()) {
            return self::respondWithNoEdit();
        }

        $uri = Craft::$app->request->getQueryParam('uri', '');

        if ($uri === '') {
            $uri = '__home__';
        }

        $model = Entry::find()->uri($uri)->one();
        if (Utils::isPluginInstalledAndEnabled('commerce') && !$model) {
            $model = Product::find()->uri($uri)->one(); // @phpstan-ignore-line
        }

        if (!$model) {
            return self::respondWithNoEdit();
        }

        $canEdit = $editService->getIsAlwaysEnabled() || Craft::$app->getElements()->canSave($model);

        if (!$canEdit) {
            return self::respondWithNoEdit();
        }

        return $this->asJson([
            'canEdit' => $canEdit,
            'editUrl' => $editService->getQuickEditUrl($model),
            'target' => $editService->getTarget(),
            'linkText' => $editService->getLinkText(),
        ]);
    }

    private function respondWithNoEdit(): Response
    {
        return $this->asJson([
            'canEdit' => false,
            'editUrl' => null,
            'target' => '_self',
            'linkText' => null,
        ]);
    }
}

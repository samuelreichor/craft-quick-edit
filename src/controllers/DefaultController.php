<?php

namespace samuelreichor\quickedit\controllers;

use Craft;
use craft\web\Controller;
use samuelreichor\quickedit\QuickEdit;
use yii\base\Response;

class DefaultController extends Controller
{
    public function actionCheckPermission(int $entryId): Response
    {
        $canEdit = false;
        if(QuickEdit::getInstance()->edit->isGlobalEnabled()) {
            $entry = Craft::$app->entries->getEntryById($entryId);

            if ($entry) {
                $canEdit = Craft::$app->getElements()->canSave($entry);
            }
        }

        return $this->asJson(['canEdit' => $canEdit]);
    }
}

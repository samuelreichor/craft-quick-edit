<?php

namespace samuelreichor\quickedit;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\controllers\UsersController;
use craft\events\FindLoginUserEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\View;
use samuelreichor\quickedit\models\Settings;
use samuelreichor\quickedit\services\EditService;
use yii\base\Event;

/**
 * quick-edit plugin
 *
 * @method static QuickEdit getInstance()
 * @method Settings getSettings()
 * @author Samuel Reichör <samuelreichor@gmail.com>
 * @copyright Samuel Reichör
 * @license MIT
 *
 * @property EditService $edit
 */
class QuickEdit extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                'edit' => ['class' => EditService::class],
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();

        Craft::$app->onInit(function() {
            $this->edit->renderQuickEdit();
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        $renderPreviewModeSetting = version_compare(Craft::$app->getVersion(), '5.6.0', '>=');

        return Craft::$app->view->renderTemplate('quick-edit/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
            'renderPreviewModeSetting' => $renderPreviewModeSetting,
        ]);
    }

    private function attachEventHandlers(): void
    {
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['quick-edit'] = __DIR__ . '/templates';
            }
        );

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'GET /quickEdit/checkPermission/<entryId>' => 'quick-edit/default/check-permission',
                ]);
            }
        );

        Event::on(
            UsersController::class,
            UsersController::EVENT_AFTER_FIND_LOGIN_USER,
            function (FindLoginUserEvent $event) {
                setcookie("ever-logged-in", "true", time() + 30 * 24 * 60 * 60);
            }
        );
    }
}

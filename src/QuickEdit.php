<?php

namespace samuelreichor\quickedit;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\controllers\UsersController;
use craft\events\PluginEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use samuelreichor\quickedit\helpers\Utils;
use samuelreichor\quickedit\models\Settings;
use samuelreichor\quickedit\services\EditService;
use samuelreichor\quickedit\variables\QuickEditVariable;
use yii\base\Event;
use yii\log\FileTarget;

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

        $this->initLogger();

        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
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

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('quickEdit', QuickEditVariable::class);
            }
        );

        /* Set cookie on local plugin installation, because you probably have admin rights.*/
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) {
                if ($event->plugin === $this && Craft::$app->getConfig()->general->devMode) {
                    $this->setLoggedInCookie();
                }
            }
        );

        Event::on(
            UsersController::class,
            UsersController::EVENT_AFTER_FIND_LOGIN_USER,
            function() {
                $this->setLoggedInCookie();
            }
        );

        if (Utils::isPluginInstalledAndEnabled('social-login')) {
            Event::on(\verbb\sociallogin\services\Users::class, \verbb\sociallogin\services\Users::EVENT_AFTER_LOGIN, // @phpstan-ignore-line
                function() {
                    $this->setLoggedInCookie();
                }
            );
        }
    }

    private function setLoggedInCookie(): void
    {
        setcookie('logged-in', 'true', [
            'expires' => 0,
            'path' => '/',
            'secure' => true,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }

    protected function initLogger(): void
    {
        $logFileTarget = new FileTarget([
            'logFile' => '@storage/logs/quick-edit.log',
            'maxLogFiles' => 10,
            'categories' => ['quick-edit'],
            'logVars' => [],
        ]);
        Craft::getLogger()->dispatcher->targets[] = $logFileTarget;
    }
}

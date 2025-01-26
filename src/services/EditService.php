<?php

namespace samuelreichor\quickedit\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\View;
use samuelreichor\quickedit\models\Settings;
use samuelreichor\quickedit\QuickEdit;
use yii\base\InvalidConfigException;

class EditService extends Component
{
    protected Settings $settings;

    public function init(): void
    {
        parent::init();
        $this->settings = QuickEdit::getInstance()->getSettings();
    }

    /**
     * Render quick link to cp based on permission and Blitz
     *
     * @return void
     * @throws InvalidConfigException
     */
    public function renderQuickEdit(): void
    {
        if (!self::isGlobalEnabled()) {
            return;
        }

        if (self::canEdit()) {
            $uri = Craft::$app->getRequest()->getFullUri();

            if ($uri === '') {
                $uri = '__home__';
            }

            $entry = Entry::find()->uri($uri)->one();

            if (!$entry) {
                return;
            }

            $html = Craft::$app->getView()->renderTemplate('quick-edit/_edit.twig', [
                'target' => self::getTarget(),
                'entryId' => $entry->id,
                'cpEditUrl' => self::getQuickEditUrl($entry),
                'linkText' => self::getLinkText(),
            ]);

            // render to page
            Craft::$app->getView()->registerHtml($html, View::POS_END);
        }
    }

    /**
     * Check if the request is in the right context to render quick edit link
     *
     * @return bool
     */
    public function canEdit(): bool
    {
        $request = Craft::$app->getRequest();
        return (
            !$request->getIsConsoleRequest() &&
            !$request->getIsCpRequest() &&
            !$request->getIsPreview() &&
            !$request->getIsLivePreview() &&
            $request->getIsSiteRequest()
        );
    }

    /**
     * Check if quick edit is enabled in settings
     *
     * @return bool
     */
    public function isGlobalEnabled(): bool
    {
        return !$this->settings->isGlobalDisabled;
    }

    /**
     * Get target for quick edit (returns _blank or _self)
     *
     * @return string
     */
    public function getTarget(): string
    {
        return $this->settings->targetBlank ? '_blank' : '_self';
    }

    /**
     * Get cp edit url (default or standalone)
     *
     * @param Entry $entry
     * @return string
     */
    public function getQuickEditUrl(Entry $entry): string
    {
        if($this->settings->isStandalonePreview) {
            return UrlHelper::cpUrl() . '/preview/' . $entry->id;
        }

        return $entry->getCpEditUrl();
    }

    /**
     * Get Link Text
     *
     * @return string
     */
    public function getLinkText(): string
    {
        return ltrim($this->settings->linkText);
    }
}

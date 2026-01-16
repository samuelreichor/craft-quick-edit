<?php

namespace samuelreichor\quickedit\services;

use Craft;
use craft\base\Component;
use craft\commerce\elements\Product;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\View;
use samuelreichor\quickedit\models\Settings;
use samuelreichor\quickedit\QuickEdit;

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
     */
    public function renderQuickEdit(): void
    {
        if (!self::canRender() || !$this->isAutoInjectEnabled()) {
            return;
        }

        $html = '<div class="craft-quick-edit"></div>';
        $html .= '<script>' . $this->getJs() . '</script>';
        $html .= '<style>' . $this->getCss() . '</style>';

        Craft::$app->getView()->registerHtml($html, View::POS_END);
    }

    /**
     * Check is enabled and in right context
     *
     * @return bool
     */
    public function canRender(): bool
    {
        if (!self::isInAllowedContext()) {
            return false;
        }

        // Bypass context and permission
        if ($this->getIsAlwaysEnabled()) {
            return true;
        }

        return self::isGlobalEnabled();
    }

    /**
     * Check if the request is in the right context to render quick edit link
     *
     * @return bool
     */
    public function isInAllowedContext(): bool
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
     * @param Entry|Product $model
     * @return string
     */
    public function getQuickEditUrl(Entry|Product $model): string // @phpstan-ignore-line
    {
        if ($this->settings->isStandalonePreview) {
            $path = UrlHelper::prependCpTrigger('preview/' . $model->id); // @phpstan-ignore-line
            return UrlHelper::cpUrl($path);
        }

        return $model->getCpEditUrl(); // @phpstan-ignore-line
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

    public function getIsAlwaysEnabled(): bool
    {
        return $this->settings->alwaysEnabled;
    }

    /**
     * Check if auto-injection is enabled
     *
     * @return bool
     */
    public function isAutoInjectEnabled(): bool
    {
        return $this->settings->autoInject;
    }

    /**
     * Get the JavaScript code for Quick Edit
     *
     * @return string
     */
    public function getJs(): string
    {
        $isAlwaysEnabled = $this->getIsAlwaysEnabled() ? 'true' : 'false';
        $uri = Craft::$app->getRequest()->getPathInfo();
        $siteUrl = UrlHelper::siteUrl();

        return <<<JS
const isAlwaysEnabled = {$isAlwaysEnabled};
if (isLikelyLoggedIn() || isAlwaysEnabled) {
  const uri = "{$uri}";
  const siteUrl = "{$siteUrl}";
  const apiUrl = siteUrl + 'actions/quick-edit/default/get-quick-edit?uri=' + encodeURIComponent(uri);
  document.addEventListener("DOMContentLoaded", async () => {
    try {
      const response = await fetch(apiUrl);
      const data = await response.json();
      if (data.canEdit) {
        const parentEl = document.querySelector('.craft-quick-edit');
        const fallbackIcon = `
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
            <path fill="currentColor"
              d="M5 19h1.425L16.2 9.225L14.775 7.8L5 17.575zm-2 2v-4.25L16.2 3.575q.3-.275.663-.425t.762-.15t.775.15t.65.45L20.425 5q.3.275.438.65T21 6.4q0 .4-.137.763t-.438.662L7.25 21zM19 6.4L17.6 5zm-3.525 2.125l-.7-.725L16.2 9.225z"/>
          </svg>
        `;
        const linkText = (data.linkText && data.linkText.trim().length > 0)
            ? data.linkText
            : fallbackIcon;
        const linkEl = document.createElement('a');
        linkEl.classList.add('craft-quick-edit_link');
        linkEl.target = data.target;
        linkEl.href = data.editUrl || '#';
        linkEl.title = "Edit Page";
        linkEl.innerHTML = linkText;
        parentEl.appendChild(linkEl);
      }
    } catch (error) {
      console.error('Quick Edit Error:', error);
    }
  });
}

function isLikelyLoggedIn() {
  return document.cookie.indexOf('logged-in=') !== -1;
}
JS;
    }

    /**
     * Get the CSS code for Quick Edit
     *
     * @return string
     */
    public function getCss(): string
    {
        return <<<CSS
.craft-quick-edit {
.craft-quick-edit_link {
    position: fixed;
    display: flex;
    top: 0.5rem;
    right: 0.5rem;
    z-index: 1000;
    background-color: black;
    color: white;
    padding: 6px;
    text-decoration: none;
    border-radius: 3px;
    transition: opacity 300ms;

    &:hover {
    opacity: 85%;
    }
}
}
CSS;
    }
}

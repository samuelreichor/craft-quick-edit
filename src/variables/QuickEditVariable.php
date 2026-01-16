<?php

namespace samuelreichor\quickedit\variables;

use samuelreichor\quickedit\QuickEdit;
use Twig\Markup;

class QuickEditVariable
{
    /**
     * Render the complete Quick Edit markup (container, JS, and CSS)
     *
     * This method is Blitz-compatible: the HTML/JS/CSS can be cached,
     * while the actual permission check happens via AJAX at runtime.
     *
     * @param string|null $nonce Optional CSP nonce for script and style tags
     * @return Markup
     */
    public function render(?string $nonce = null): Markup
    {
        if (!QuickEdit::getInstance()->edit->canRender()) {
            return new Markup('', 'UTF-8');
        }

        $nonceAttr = $nonce ? ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"' : '';

        $html = '<div class="craft-quick-edit"></div>';
        $html .= '<script' . $nonceAttr . '>' . $this->js() . '</script>';
        $html .= '<style' . $nonceAttr . '>' . $this->css() . '</style>';

        return new Markup($html, 'UTF-8');
    }

    /**
     * Get the raw JavaScript code (without script tags)
     *
     * @return string
     */
    public function js(): string
    {
        return QuickEdit::getInstance()->edit->getJs();
    }

    /**
     * Get the raw CSS code (without style tags)
     *
     * @return string
     */
    public function css(): string
    {
        return QuickEdit::getInstance()->edit->getCss();
    }
}

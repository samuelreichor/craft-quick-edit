<?php

namespace samuelreichor\quickedit\models;

use craft\base\Model;

/**
 * quick-edit settings
 */
class Settings extends Model
{
    // Globally disable or enable the plugin
    public bool $isGlobalDisabled = false;

    // Set target _blank on link
    public bool $targetBlank = false;

    // Opens the quick edit link in the standalone preview
    public bool $isStandalonePreview = false;

    // Set a link text for the quick edit
    public string $linkText = '';

    // Always enable the quick edit for development purposes
    public bool $alwaysEnabled = true;
}

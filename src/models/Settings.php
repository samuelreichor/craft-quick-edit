<?php

namespace samuelreichor\quickedit\models;

use craft\base\Model;

/**
 * quick-edit settings
 */
class Settings extends Model
{
    public bool $isGlobalDisabled = false;
    public bool $targetBlank = false;
    public bool $isStandalonePreview = false;
    public string $linkText = '';
}

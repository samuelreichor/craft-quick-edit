<?php

namespace samuelreichor\quickedit\models;

use Craft;
use craft\base\Model;

/**
 * quick-edit settings
 */
class Settings extends Model
{
    public bool $isGlobalDisabled = false;
    public bool $targetBlank = false;
    public bool $isStandalonePreview = false;
}

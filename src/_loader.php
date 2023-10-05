<?php
/**
 * Loader file for HOOK
 *
 * Define some default hooks.
 * Hooks are now deprecated and will be removed in a future released.
 * 
 * @author Florent Hazard <contact@sowapps.com>
 * Some predefined hooks are specified in this file, it serves for the core of Orpheus.
 */

use Orpheus\Hook\Hook;

// Checking module
define('HOOK_CHECKMODULE', 'checkModule');
Hook::create(HOOK_CHECKMODULE);

// Show rendering
define('HOOK_SHOWRENDERING', 'showRendering');
Hook::create(HOOK_SHOWRENDERING);

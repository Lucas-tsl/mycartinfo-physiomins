<?php

function upgrade_module_1_2_1($module)
{
    return $module->registerHook('displayMyCartInfoBanner')
        && $module->registerHook('displayMyCartInfoProducts');
}

<?php
// Lumener and $plugins are already defined before this file is included
class ExtendedLumener extends Lumener
{
    public function permanentLogin()
    {
        // key used for permanent login
        return 'ca41d8e9879df648e9a43cefa97bc12d';
    }
}

if (empty($plugins)) {
    return new ExtendedLumener();
}
return new ExtendedLumener($plugins);

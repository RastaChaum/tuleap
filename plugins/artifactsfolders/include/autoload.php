<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoloadee97577b5260f7b712559bd58bbdf171($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'artifactsfoldersplugin' => '/artifactsfoldersPlugin.class.php',
            'tuleap\\artifactsfolders\\artifactsfoldersplugindescriptor' => '/ArtifactsFoldersPluginDescriptor.php',
            'tuleap\\artifactsfolders\\artifactsfoldersplugininfo' => '/ArtifactsFoldersPluginInfo.php',
            'tuleap\\artifactsfolders\\nature\\natureisfolderpresenter' => '/Nature/NatureIsFolderPresenter.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoloadee97577b5260f7b712559bd58bbdf171');
// @codeCoverageIgnoreEnd

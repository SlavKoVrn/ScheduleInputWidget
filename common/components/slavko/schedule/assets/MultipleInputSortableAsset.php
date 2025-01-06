<?php

/**
 * @link https://github.com/unclead/yii2-multiple-input
 * @copyright Copyright (c) 2014 unclead
 * @license https://github.com/unclead/yii2-multiple-input/blob/master/LICENSE.md
 */

namespace slavko\schedule\assets;

use yii\web\AssetBundle;

/**
 * Class MultipleInputAsset
 * @package unclead\multipleinput\assets
 */
class MultipleInputSortableAsset extends AssetBundle
{
    public $depends = [
        'slavko\schedule\assets\MultipleInputAsset',
    ];

    public function init()
    {
        $this->sourcePath = __DIR__ . '/src/';

        $this->js = [
            YII_DEBUG ? 'js/sortable.js' : 'js/sortable.min.js'
        ];

        $this->css = [
            YII_DEBUG ? 'css/sorting.css' : 'css/sorting.min.css'
        ];

        parent::init();
    }
} 
<?php


namespace andru19\fancytree;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * The yii2-fancytree-widget is a Yii 2 wrapper for the fancytree.js
 * See more: https://github.com/mar10/fancytree
 *

 */
class FancytreeWidget extends InputWidget
{
    const SELECT_SINGLE = 1;
    const SELECT_MULTI = 2;
    const SELECT_MULTI_HIER = 3;

    const CLICK_ACTIVATE = 1;
    const CLICK_EXPAND = 2;
    const CLICK_ACTIVATE_EXPAND = 3;
    const CLICK_DBL_EXPAND = 4;

    /**
     * @var bool Make sure that the active node is always visible, i.e. its parents are expanded
     */
    public $activeVisible = true;
    /**
     * @var array Default options for ajax requests
     */
    public $ajax = [];
    /**
     * @var bool Add WAI-ARIA attributes to markup
     */
    public $aria = false;
    /**
     * @var bool Activate a node when focused with the keyboard
     */
    public $autoActivate = true;
    /**
     * @var bool Automatically collapse all siblings, when a node is expanded
     */
    public $autoCollapse = false;
    /**
     * @var bool Scroll node into visible area, when focused by keyboard
     */
    public $autoScroll = false;
    /**
     * @var bool Display checkboxes to allow selection
     */
    public $checkbox = false;
    /**
     * @var int Defines what happens, when the user click a folder node. 1:activate, 2:expand, 3:activate and expand, 4:activate/dblclick expands
     */
    public $clickFolderMode = self::CLICK_DBL_EXPAND;
    /**
     * @var null|int 0..2 (null: use global setting $.ui.fancytree.debugInfo)
     */
    public $debugLevel = null;
    /**
     * @var null|string|JsExpression callback(node) is called for ner nodes without a key. Must return a new unique key. (default null: generates default keys like that: "_" + counter)
     */
    public $defaultKey = null;
    /**
     * @var bool Accept passing ajax data in a property named `d`
     */
    public $enableAspx = true;
    /**
     * @var array List of active extensions
     */
    public $extensions = [];
    /**
     * @var array Animation options, null:off
     */
    public $fx = ['height' => 'toggle', 'duration' => 200];
    /**
     * @var bool Add `id="..."` to node markup
     */
    public $generateIds = true;
    /**
     * @var bool Display node icons
     */
    public $icons = true;
    /**
     * @var string
     */
    public $idPrefix = 'ft_';
    /**
     * @var string|null Path to a folder containing icons (default: null, using 'skin/' subdirectory)
     */
    public $imagePath = null;
    /**
     * @var bool Support keyboard navigation
     */
    public $keyboard = true;
    /**
     * @var string
     */
    public $keyPathSeparator = '/';
    /**
     * @var int 2: top-level nodes are not collapsible
     */
    public $minExpandLevel = 1;
    /**
     * @var array optional margins for node.scrollIntoView()
     */
    public $scrollOfs = ['top' => 0, 'bottom' => 0];
    /**
     * @var string jQuery scrollable container for node.scrollIntoView()
     */
    public $scrollParent = '$container';
    /**
     * @var int 1:single, 2:multi, 3:multi-hier
     */
    public $selectMode = self::SELECT_MULTI;
    /**
     * @var array Used to Initialize the tree
     */
    public $source;
    /**
     * @var id of parent category (optional)
     */
    public $parent;
    /**
     * @var array Translation table
     */
    public $strings;
    /**
     * @var bool Add tabindex='0' to container, so tree can be reached using TAB
     */
    public $tabbable = true;
    /**
     * @var bool Add tabindex='0' to node title span, so it can receive keyboard focus
     */
    public $titlesTabbable = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->registerAssets();
        parent::init();
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        FancytreeAsset::register($view);
        $id = 'fancyree_' . $this->id;
        if (isset($this->options['id'])) {
            $id = $this->options['id'];
            unset($this->options['id']);
        } else {
            echo Html::tag('div', '', ['id' => $id]);
        }
        $options = Json::encode(ArrayHelper::merge([
            'activeVisible' => $this->activeVisible,
            'ajax' => $this->ajax,
            'aria' => $this->aria,
            'autoActivate' => $this->autoActivate,
            'autoCollapse' => $this->autoCollapse,
            'autoScroll' => $this->autoScroll,
            'checkbox' => $this->checkbox,
            'clickFolderMode' => $this->clickFolderMode,
            'debugLevel' => $this->debugLevel,
            'defaultKey' => $this->defaultKey,
            'enableAspx' => $this->enableAspx,
            'extensions' => $this->extensions,
            'fx' => $this->fx,
            'generateIds' => $this->generateIds,
            'icons' => $this->icons,
            'idPrefix' => $this->idPrefix,
            'imagePath' => $this->imagePath,
            'keyboard' => $this->keyboard,
            'keyPathSeparator' => $this->keyPathSeparator,
            'minExpandLevel' => $this->minExpandLevel,
            'scrollOfs' => $this->scrollOfs,
            'scrollParent' => $this->scrollParent,
            'selectMode' => $this->selectMode,
            'source' => $this->source,
            'parent' => $this->parent,
            'strings' => $this->strings,
            'tabbable' => $this->tabbable,
            'titlesTabbable' => $this->titlesTabbable,
        ], $this->options));
        $view->registerJs('$("#' . $id . '").fancytree( ' . $options . ')');
        if ($this->hasModel() || $this->name !== null) {
            $name = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name;
            $selected = $this->selectMode == self::SELECT_SINGLE ? 'undefined' : "\"{$name}\"";
            $active = $this->selectMode == self::SELECT_SINGLE ? $name : 'undefined';
            $view->registerJs('$("#' . $id . '").parents("form").submit(function(){$("#' . $id . '").fancytree("getTree").generateFormElements(' . $selected . ', ' . $active . ')});');

            if (!empty($this->parent && $this->model->id)) {
                $view->registerJs('$("#' . $id . '").fancytree("getTree").activateKey("' . $this->model->id . '");');
                $view->registerJs('$("#' . $id . '").fancytree("getTree").getNodeByKey("' . $this->parent . '").setSelected(true)');
            } elseif ($this->model->id) {
                $attribute = $this->attribute;
                $view->registerJs('$("#' . $id . '").fancytree("getTree").activateKey("' . $this->model->$attribute . '");');
                $view->registerJs('$("#' . $id . '").fancytree("getTree").getNodeByKey("' . $this->model->$attribute . '").setSelected(true)');
            }
        }
    }
}
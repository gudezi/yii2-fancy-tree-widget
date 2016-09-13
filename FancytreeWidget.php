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
     * @var bool Defines if quick search activated
     */
    public $quicksearch = false;
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
     * @var array Animation options, null:off gudezi change property fx by deprecated
     */
    public $toggleEffect = ['height' => 'toggle', 'duration' => 200];
    /**
     * @var bool Add `id="..."` to node markup
     */
    public $generateIds = true;
    /**
     * @var bool Display node icons gudezi chanche property icons by deprecated
     */
    public $icon = true;
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
     * @var id field table
     */
    public $idfield = 'id';
    /**
     * @var array Translation table
     */
    public $strings;
    /**
     * @var bool Add tabindex='0' to container, so tree can be reached using TAB gudezi chenge property tabbable by deprecated
     */
    public $tabindex = 0;
    /**
     * @var bool Add tabindex='0' to node title span, so it can receive keyboard focus
     */
    public $titlesTabbable = false;
    /**
     * @var bool show button expand all add by gudezi
     */
    public $btnExpandAll = false;
    /**
     * @var bool show button collapse all add by gudezi
     */
    public $btnCollapseAll = false;
    /**
     * @var bool show button toggle expand add by gudezi
     */
    public $btnToggleExpand = false;
    /**
     * @var bool show button select all add by gudezi
     */
    public $btnSelectAll = false;
    /**
     * @var bool show button unselect all add by gudezi
     */
    public $btnUnselectAll = false;
    /**
     * @var bool show button toggle select add by gudezi
     */
    public $btnToggleSelect = false;
    /**
     * @var bool show child counter add by gudezi
     */
    public $childcounter = false;
    /**
     * @var bool show glyph icons add by gudezi
     */
    public $glyph = false;
    /**
     * @var bool show filter add by gudezi
     */
    public $filter = false;
    
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
        echo "<div>";
        if($this->filter){
            echo "<label>Filter:</label><input name='".$idPrefix."search' id='".$idPrefix."search' placeholder='Filter...' autocomplete='off'><button id='".$idPrefix."btnResetSearch'>&times;</button><span id='".$idPrefix."matches'></span>";
        }
 		echo "</div>";

        echo "<div>";
        if($this->btnExpandAll)	echo "<button id='".$idPrefix."btnExpandAll' class='btn btn-xs btn-primary'>Expand All</button>";
        
        if($this->btnCollapseAll) echo "<button id='".$idPrefix."btnCollapseAll' class='btn btn-xs btn-warning'>Collapse All</button>";

        if($this->btnToggleExpand) echo "<button id='".$idPrefix."btnToggleExpand' class='btn btn-xs btn-info'>Toggle Expand</button>";

        if($this->btnSelectAll && $this->selectMode == self::SELECT_MULTI)
            echo "<button id='".$idPrefix."btnSetAll' class='btn btn-xs btn-primary'>Select All</button>";

        if($this->btnUnselectAll && $this->selectMode == self::SELECT_MULTI)
            echo "<button id='".$idPrefix."btnUnsetAll' class='btn btn-xs btn-warning'>Unselect All</button>";

        if($this->btnToggleSelect && $this->selectMode == self::SELECT_MULTI)
            echo "<button id='".$idPrefix."btnToggleSelect' class='btn btn-xs btn-info'>Toggle Select</button>";
		echo "</div>";

        $view = $this->getView();
        
        FancytreeAsset::register($view);
        $id = 'fancyree_' . $this->id;
        if (isset($this->options['id'])) {
            $id = $this->options['id'];
            unset($this->options['id']);
        } else {
            echo Html::tag('div', '', ['id' => $id]);
        }
        
        if($this->childcounter)
        {
            if(array_search('childcounter',$this->extensions)<1)
            {
                array_push($this->extensions,'childcounter');
            }
            $childcounter = array();
            $childcounter["deep"]=true;
            $childcounter["hideZeros"]=true;
            $childcounter["hideExpanded"]=true;
        }
        if($this->glyph)
        {
            if(array_search('glyph',$this->extensions)<1)
            {
                array_push($this->extensions,'glyph');
            }
            $map = array();
            $map['doc'] = "glyphicon glyphicon-file";
            $map['docOpen'] = "glyphicon glyphicon-file";
            $map['checkbox'] = "glyphicon glyphicon-unchecked";
            $map['checkboxSelected'] = "glyphicon glyphicon-check";
            $map['checkboxUnknown'] = "glyphicon glyphicon-share";
            $map['dragHelper'] = "glyphicon glyphicon-play";
            $map['dropMarker'] = "glyphicon glyphicon-arrow-right";
            $map['error'] = "glyphicon glyphicon-warning-sign";
            $map['expanderClosed'] = "glyphicon glyphicon-menu-right";
            $map['expanderLazy'] = "glyphicon glyphicon-menu-right";  // glyphicon-plus-sign
            $map['expanderOpen'] = "glyphicon glyphicon-menu-down";  // glyphicon-collapse-down
            $map['folder'] = "glyphicon glyphicon-folder-close";
            $map['folderOpen'] = "glyphicon glyphicon-folder-open";
            $map['loading'] = "glyphicon glyphicon-refresh glyphicon-spin";
          
            $glyph_opts = array();
            $glyph_opts['map']=$map;
        }
        if($this->filter)
        {
            if(array_search('filter',$this->extensions)<1)
            {
                array_push($this->extensions,'filter');
            }
            $this->quicksearch = true;

            $filter = array();
            $filter["autoExpand"]=true;
            $filter["leavesOnly"]=true;
            $filter["autoApply"]=true; // Re-apply last filter if lazy data is loaded
            $filter["counter"]=true; // Show a badge with number of matching child nodes near parent icons
            $filter["fuzzy"]=false; // Match single characters in order, e.g. 'fb' will match 'FooBar'
            $filter["hideExpandedCounter"]=true;// Hide counter badge, when parent is expanded
            $filter["highlight"]=true; // Highlight matches by wrapping inside <mark> tags
            $filter["mode"]="dimm"; // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
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
            'toggleEffect' => $this->toggleEffect,
            'generateIds' => $this->generateIds,
            'icon' => $this->icon,
            'idPrefix' => $this->idPrefix,
            'imagePath' => $this->imagePath,
            'keyboard' => $this->keyboard,
            'keyPathSeparator' => $this->keyPathSeparator,
            'minExpandLevel' => $this->minExpandLevel,
            'scrollOfs' => $this->scrollOfs,
            'scrollParent' => $this->scrollParent,
            'selectMode' => $this->selectMode,
            'source' => $this->source,
            'quicksearch' => $this->quicksearch,
            'parent' => $this->parent,
            'strings' => $this->strings,
            'tabindex' => $this->tabindex,
            'titlesTabbable' => $this->titlesTabbable,
            'childcounter' => $childcounter,
            'glyph' => $glyph_opts,
            'filter' => $filter,
        ], $this->options));
        $view->registerJs('$("#' . $id . '").fancytree( ' . $options . ')');
        if ($this->hasModel() || $this->name !== null) {
            $name = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name;

			if($this->selectMode != self::SELECT_SINGLE)
				$name = $name.'[]';

            //$selected = $this->selectMode == self::SELECT_SINGLE ? 'undefined' : "\"{$name}\"";
            $selected = $this->selectMode == self::SELECT_SINGLE ? "\"{$name}\"" : "\"{$name}\"";
            //$active = $this->selectMode == self::SELECT_SINGLE ? "\"{$name}\"" : 'undefined';
            $active = 'undefined';
            //$active = $this->selectMode == self::SELECT_SINGLE ? $name : 'undefined';
            
            if($this->btnExpandAll)
                $view->registerJs('$("#'.$idPrefix.'btnExpandAll").click(function(event){event.preventDefault(); $("#'.$id.'").fancytree("getRootNode").visit(function(node){node.setExpanded(true);});});');
            
            if($this->btnCollapseAll)
                $view->registerJs('$("#'.$idPrefix.'btnCollapseAll").click(function(event){event.preventDefault(); $("#'.$id.'").fancytree("getRootNode").visit(function(node){node.setExpanded(false);});});');

			if($this->btnToggleExpand)
				$view->registerJs('$("#'.$idPrefix.'btnToggleExpand").click(function(event){event.preventDefault(); $("#'.$id.'").fancytree("getRootNode").visit(function(node){node.toggleExpanded();});});');
            
            if($this->btnSelectAll && $this->selectMode == self::SELECT_MULTI)
                $view->registerJs('$("#'.$idPrefix.'btnSetAll").click(function(event){event.preventDefault(); $("#'.$id.'").fancytree("getTree").visit(function(node){node.setSelected(true);});});');
        
            if($this->btnUnselectAll && $this->selectMode == self::SELECT_MULTI)
                $view->registerJs('$("#'.$idPrefix.'btnUnsetAll").click(function(event){event.preventDefault(); $("#'.$id.'").fancytree("getTree").visit(function(node){node.setSelected(false);});});');

            if($this->btnToggleSelect && $this->selectMode == self::SELECT_MULTI)
                $view->registerJs('$("#'.$idPrefix.'btnToggleSelect").click(function(event){event.preventDefault(); $("#'.$id.'").fancytree("getRootNode").visit(function(node){node.toggleSelected();});});');

			if($this->filter){
                $view->registerJs('$("#'.$idPrefix.'search").keyup(function(e){e.preventDefault(); var n,opts = {autoExpand:'.$filter["autoExpand"].',leavesOnly: '.$filter["leavesOnly"].'},match = $(this).val();if(e && e.which === $.ui.keyCode.ESCAPE || $.trim(match) === ""){$("#'.$idPrefix.'btnResetSearch").click();return;}n = $("#' . $id . '").fancytree("getTree").filterNodes(match, opts);$("#'.$idPrefix.'btnResetSearch").attr("disabled", false);$("#'.$idPrefix.'matches").text("(" + n + " matches)");}).focus();');
                
                $view->registerJs('$("#'.$idPrefix.'btnResetSearch").click(function(e){e.preventDefault();$("#'.$idPrefix.'search").val("");$("#'.$idPrefix.'matches").text("");$("#' . $id . '").fancytree("getTree").clearFilter();}).attr("disabled", true);');
            }
            
            $view->registerJs('$("#' . $id . '").parents("form").submit(function(){$("#' . $id . '").fancytree("getTree").generateFormElements(' . $selected . ', ' . $active . ')});');
            
            $idfield = $this->idfield; 
            
            if (!empty($this->parent && $this->model->$idfield)) {
                $view->registerJs('$("#' . $id . '").fancytree("getTree").activateKey("' . $this->model->$idfield . '");');
                $view->registerJs('$("#' . $id . '").fancytree("getTree").getNodeByKey("' . $this->parent . '").setSelected(true)');
            } elseif ($this->model->$idfield) {
                $attribute = $this->attribute;
                if($this->selectMode == self::SELECT_SINGLE)
                {
                    $view->registerJs('$("#' . $id . '").fancytree("getTree").activateKey("'.$this->model->$attribute.'");');
                    $view->registerJs('$("#' . $id . '").fancytree("getTree").getNodeByKey("'.$this->model->$attribute.'").setSelected(true)');
                }
                else
                {
                    foreach($this->model->$attribute as $node)
                    {
                        $view->registerJs('$("#' . $id . '").fancytree("getTree").activateKey("'.$node.'");');
                        $view->registerJs('$("#' . $id . '").fancytree("getTree").getNodeByKey("'.$node.'").setSelected(true)');
                    }
                }
            }
        }
    }
}
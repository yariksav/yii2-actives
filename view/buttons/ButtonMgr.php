<?php

namespace yariksav\actives\view\buttons;

use yii;
use yii\base;
use yii\base\Object;
use yariksav\actives\base\Component;
use yariksav\actives\base\Collection;


class ButtonMgr extends Collection
{

    public static $builtInColumns = [
        'row' => 'yariksav\actives\view\buttons\RowButton'
    ];

    protected function createItem($params, $name = null) {
        if (!$name || is_int($name)) {
            throw new \Exception('Please get the name for control');
        }

        if (empty($params['type']) && empty($params['class'])) {
            $params['class'] = Button::className();
        }

        if (isset($params['type'])) {
            $type = $params['type'];
            if (isset(static::$builtInColumns[$type])) {
                $type = static::$builtInColumns[$type];
            }
            if (is_array($type)) {
                $params = array_merge($type, $params);
            } else {
                $params['class'] = $type;
            }
        }
        return parent::createItem($params, $name);
    }

    protected function prepareButtons(){
        $this->_buttons = isset($this->buttons) ? $this->evaluateExpression($this->buttons, ['grid'=>$this]) : $this->buttons();
    }

    public function build() {
        return $this->renderButtons();
    }

    public function buildRow($data) {
        return $this->renderButtons($data);
    }

    protected function renderButtons($model = false){
        $result = [];
        if ($this->_collection) foreach ($this->_collection as $name=>$button) {

//            if ($model === false && $button instanceof RowButton) {
//                continue;
//            }
//            if ($model !== false && !($button instanceof RowButton)) {
//                continue;
//            }

            if (!$button->isVisible($model) || !$button->hasPermissions()) {
                continue;
            }

            $btn = $button->build($model);

//            if (isset($button->buttons)){
//                $btn['buttons'] = $this->renderButtons($button->buttons, $data);
//            }
            $result[$name] = $btn;
        }
        return $result;
    }

}
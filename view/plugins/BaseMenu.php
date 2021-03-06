<?php
namespace yariksav\actives\view\plugins;

use yariksav\actives\view\buttons\ButtonMgr;
use yii;


class BaseMenu extends Plugin
{

    protected $_actions;

    function __construct($owner, $config = []) {
        $this->_actions = new ButtonMgr();
        parent::__construct($owner, $config);
    }

    public function setActions($value){
        if (is_callable($value)) {
            
        }
        $this->_actions->load($value);
    }

    public function build() {
        return array_merge(parent::build(), [
            'actions'=>$this->_actions->build(),
        ]);
    }
}

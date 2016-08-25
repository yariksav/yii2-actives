<?php
/**
 * Created by PhpStorm.
 * User: Savaryn Yaroslav
 * Date: 21.05.2016
 * Time: 10:11
 */

namespace yariksav\actives\controls;



use yii;
use Closure;
use yii\base\Model;
use yii\base\Object;
use yii\helpers\ArrayHelper;

use yariksav\actives\base\Component;
use yariksav\actives\base\PermissionTrait;
use yariksav\actives\base\VisibleTrait;

class Control extends Component
{
    use PermissionTrait;
    use VisibleTrait;

    public $config = [];
    public $save;
    public $afterSave;
    public $default;
    public $validate;
    public $data;
    public $options;
    public $isAjaxLoad = false;

    /**
     * @var boolean Is model riquired for build control data. It needs for some active controls as self update logic
     */
    public $requireModel = false;

    /**
     * @var string|Closure the caption or label of the control
     * @see getLabel
     */
    protected $owner;
    protected $_label;
    protected $_text;
    protected $_name;
    protected $_type;
    protected $_value;
    protected $_model;

    /**
     * Constructor.
     * @param ActiveObject $owner the active object whom belongs this control
     * @param array $config configurations to be applied to the newly created query object
     */
    function __construct($owner, $model = null, $config = []) {
        parent::__construct($config);
        $this->owner = $owner;
        $this->_model = $model;

    }

    public function getModel() {
        return $this->_model;
    }

    public function setModel($value) {
        $this->_model = $value;
        if ($this->_model instanceof Model && is_callable($this->validate)) {
            $this->_model->on(yii\base\Model::EVENT_BEFORE_VALIDATE, $this->validate);
        }
    }

    public function getValue() {
        $name = $this->_name;
        if (is_callable($this->_value)) {
            $this->_value = call_user_func_array($this->_value, [
                'data' => $this->_model,
                'owner' => $this->owner
            ]);
        } else if ($this->_model instanceof yii\db\BaseActiveRecord && $this->_model->hasAttribute($name)) {
            $this->_value = $this->_model->$name;
        } else if (!$this->_value) {
            $this->_value = isset($this->_model) && isset($this->_model[$name]) ? $this->_model[$name] : $this->default;
        }
        return $this->_value;
    }

    public function setValue($value) {
        $this->_value = $value;
    }

    public function getVisible() {
        if (is_callable($this->_visible)) {
            $this->_visible = call_user_func_array($this->_visible, [
                'data' => $this->_model,
                'owner' => $this->owner
            ]);
        }
        return $this->_visible;
    }

    public function getType() {
        return $this->_type;
    }

    public function setType($value) {
        $this->_type = $value;
    }

    public function getText() {
        if (is_callable($this->_text)) {
            $this->_text = call_user_func_array($this->_text, [
                'data' => $this->_model,
                'owner' => $this->owner
            ]);
        } else if (!$this->_text) {
            if ($this->_model instanceof Model) {
                $labels = $this->_model->attributeLabels();
                $this->_text = ArrayHelper::getValue($labels, $this->_name);
            }
        }
        return $this->_text;
    }

    public function setText($value) {
        $this->_text = $value;
    }

    /**
     * Returns the name for the control.
     * @return array the options
     */
    public function getName() {
        return $this->_name;
    }

    public function setName($value) {
        $this->_name = $value;
    }

    /**
     * Build control output array.
     * @return array the control data
     */
    public function build() {
        $control = [
            'type' => $this->_type,
            'name' => $this->_name,
            'value' => $this->value,
        ];
        $text = $this->text;
        if ($text) {
            $control['text'] = $text;
        }
        if ($this->options) {
            $control['options'] = $this->options;
        }
        return array_merge($control, $this->load());
    }

    public function load() {
        return [];
    }

    public function update($value) {
        $this->_value = $value;
        $name = $this->_name;
        $model = $this->_model;

        if (is_callable($this->save)) {
            call_user_func_array($this->save, [
                'value' => $value,
                'model' => $model
            ]);
        } else {

            if (isset($model)) {
                if ($model instanceof Model) {
                    if ($model->isAttributeActive($name)) {
                        $model->$name = $value;
                    }
                } else {
                    $model[$name] = $value;
                }
            }
        }
    }
}
<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('IndeedForms')){
	 return;
}
class IndeedForms{
	/**
	 * @var array
	 */
	private $attr = array();
	public function __construct(){}
	/**
	 * @param string
	 * @param mixed
	 * @return none
	 */
	public function __set($name='', $value=null){
		$this->$name = $value;
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getOutput(){
		$output = '';
		$this->checkInputVariables();
		/// label
		$output = $this->printLabel();
		///field
		switch ($this->attr['type']){
			case 'text':
				$output .= "<input type='text' name='{$this->attr['name']}' value=\"{$this->attr['value']}\" id='{$this->attr['id']}' class='{$this->attr['classes']}' {$this->attr['field_extra_attributes']} />";
				break;
			case 'textarea':
				$output .= "<textarea name='{$this->attr['name']}' id='{$this->attr['id']}' class='{$this->attr['classes']}' data-field_type='textarea' {$this->attr['field_extra_attributes']} >{$this->attr['value']}</textarea>";
				break;
			case 'number':
				$output .= "<input type='number' name='{$this->attr['name']}' value=\"{$this->attr['value']}\" id='{$this->attr['id']}' class='{$this->attr['classes']}' {$this->attr['field_extra_attributes']} />";
				break;
			case 'hidden':
				$output .= "<input type='hidden' name='{$this->attr['name']}' value=\"{$this->attr['value']}\" id='{$this->attr['id']}' class='{$this->attr['classes']}' {$this->attr['field_extra_attributes']} />";
				break;
			case 'checkbox':
				foreach ($this->attr['options'] as $value=>$label){
					$checked = $this->attr['value']==$value ? 'checked' : '';
					$output .= "<div class='{$this->attr['item_wrapper_class']}'>";
					$output .= "<input type='checkbox' name='{$this->attr['name']}[]' value=\"{$value}\" class='{$this->attr['classes']}' {$this->attr['field_extra_attributes']} $checked />" . $label;
					$output .= "</div>";
				}
				break;
			case 'radio':
				foreach ($this->attr['options'] as $key=>$label){
					$checked = $this->attr['value']===$key ? 'checked' : '';
					$output .= "<div class='{$this->attr['item_wrapper_class']}'>";
					$output .= "<input type='radio' name='{$this->attr['name']}' value=\"{$key}\"  class='{$this->attr['classes']}' {$this->attr['field_extra_attributes']} $checked />" . $label;
					$output .= "</div>";
				}
				break;
			case 'select':
				$output .= "<select class='{$this->attr['field_wrapper_class']}' name='{$this->attr['name']}' id='{$this->attr['id']}' {$this->attr['field_extra_attributes']} >";
				foreach ($this->attr['options'] as $value=>$label){
					$selected = $this->attr['value']==$value ? 'selected' : '';
					$output .= "<option value=\"{$value}\" $selected >" . $label . '</option>';
				}
				$output .= "</select>";
				break;
			case 'sorting':
				$view = new ViewUlp();
				$view->setTemplate(ULP_PATH . 'views/templates/sorting_field_type.php');
				$view->setContentData($this->attr);
				$output .= $view->getOutput();
				break;
			case 'on_off_button':
				break;
			case 'fill_in_type':
				$output .= $this->FillInRemovePosibleAnswersFromString($this->attr['fullString'], $this->attr['possibleAnswers'], $this->attr['name'], 'ulp-text-fill-in');
				break;
			case 'images-single-choice':
				$view = new ViewUlp();
				$settings = $this->attr;
				$settings['type'] = 'images-single-choice';
				$view->setContentData($settings);
				$view->setTemplate(ULP_PATH . 'views/templates/form_fields.php');
				$output .= $view->getOutput();
				break;
			case 'images-multiple-choice':
				$view = new ViewUlp();
				$view->setTemplate(ULP_PATH . 'views/templates/form_fields.php');
				$settings = $this->attr;
				$settings['type'] = 'images-multiple-choice';
				$view->setContentData($settings);
				$output .= $view->getOutput();
				break;
			case 'matching':
				$view = new ViewUlp();
				$view->setTemplate(ULP_PATH . 'views/templates/form_fields.php');
				$settings = $this->attr;
				$settings['type'] = 'matching';
				$view->setContentData($settings);
				$output .= $view->getOutput();
				break;
		}
		///wrapper
		$output = $this->putIntoWrapper($output);
		$this->resetClassProperties();
		return $output;
	}
	/**
	 * @param none
	 * @return string
	 */
	private function printLabel(){
		if (empty($this->attr['label'])){
			 return '';
		}
		$output = "<label class='{$this->attr['label_class']}' id='{$this->attr['label_id']}' {$this->attr['label_attributes']} >";
		$output .= $this->attr['label'];
		$output .= '</label>';
		return $output;
	}
	/**
	 * @param string
	 * @return string
	 */
	private function putIntoWrapper($output=''){
		if ($this->attr['wrapper']){
			$output = "<div class='{$this->attr['wrapper_class']}' id='{$this->attr['wrapper_id']}' {$this->attr['wrapper_extra_attributes']}>" . $output . "</div>";
		}
		return $output;
	}

	public function FillInRemovePosibleAnswersFromString($string='', $possibleAnswers=[], $name='', $class='')
	{
			foreach ($possibleAnswers as $key => $value) {
					$string = str_replace('{'.$value.'}', " <input type='text' name='".$name."[]' value='' class='$class' data-field_type='fill_in'/> ", $string);
			}
			return $string;
	}

	/**
	 * @param none
	 * @return none
	 */
	private function checkInputVariables(){
		$standard_args = array(
								'name',
								'id',
								'value',
								'classes',
								'other_args',
								'disabled',
								'placeholder',
								'multiple_values',
								'sublabel',
								'field_extra_attributes',
								'field_wrapper_class',
								'item_wrapper_class',
								'label',
								'label_class',
								'label_id',
								'label_attributes',
								'wrapper',
								'wrapper_class',
								'wrapper_id',
								'wrapper_extra_attributes',
		);
		foreach ($standard_args as $k){
			if (!isset($this->attr[$k])){
				$this->attr[$k] = '';
			}
		}
	}
	/**
	 * @param none
	 * @return none
	 */
	private function resetClassProperties(){
		$vars = get_class_vars(__CLASS__);
		foreach ($vars as $key => $value){
			$this->$key = null;
		}
	}
}

<?php 
	function formOutput($formId, $return='./thanks.html', $failure='./return.html', $submit='Submit'){
		global $db;

		$formId = (int)$formId;
		$fields = $db->query_assoc($db->prepare('SELECT * FROM form_template_fields WHERE form_template_id=:formid ORDER BY sort', array('formid'=>$formId)));

		if(!$fields)
			return;

		echo '<form method="post" action="./form-proc-form.html" class="userform" id="form-'.$formId.'">';
			//Message Handling
			if(isset($_GET['msg'])){
				echo '<div class="form-message">';
				if($_GET['msg']=='success')
					echo '<div class="success"><h2>Thank You!</h2><p>Form successfully submitted.</p></div>';
				else if($_GET['msg']=='invalidform')
					echo '<div class="invalid"><h2>Opps!</h2><p>There was a problem please try again.</p></div>';
				echo '</div>';
			}

			// Processing requirements
			echo '<input type="hidden" name="form-id" value="'.$formId.'">';
			echo '<input type="hidden" name="return-to" value="'.$return.'?msg=success">';
			echo '<input type="hidden" name="process-fail" value="'.$failure.'">';

			// Spam prevention
			echo '<div style="display: none;">';
				echo '<input type="text" name="test1" id="f-'.$formId.'-formtest1" value="">';
				echo '<input type="text" name="test2" id="f-'.$formId.'-formtest2" value="">';
				echo '<script>';
					echo 'document.getElementById(\'f-'.$formId.'-formtest2\').value = \'filled\';';
				echo '</script>';
			echo '</div>';

			// Field output
			echo '<dl>';
			foreach ($fields as $key => $field) {
				switch ($field['input']) {
					case 'text':
						outputTextField($field);
						break;

					case 'textarea':
						outputTextAreaField($field);
						break;

					case 'radio':
						outputRadioField($field);
						break;

					case 'checkbox':
						outputCheckboxField($field);
						break;

					case 'select':
						outputSelectField($field);
						break;

					case 'file':
						outputFileField($field);
						break;

					case 'heading':
						outputHeadingField($field);
						break;
					
					default:
						outputTextField($field);
						break;
				}
			}
			echo '</dl>';

			echo '<input type="submit" value="'.$submit.'" />';

		echo '</form>';
	}

	function outputTextField($field){
		$options = json_decode($field['options']);
		$class = 'f-'.$field['form_template_id'].'-'.$field['form_template_field_id'];
		echo '<dt class="'.$class.'"><label for="'.$field['name'].'">'.$field['text'].'</label></dt>';
		echo '<dd class="'.$class.' textfield"><input type="text" name="'.$field['name'].'" id="'.$field['name'].'" value="" '.($field['required'] ? 'required' : '').' /></dd>';
		 outputFieldOptionText($field, $options);
	}

	function outputTextAreaField($field){
		$options = json_decode($field['options']);
		$class = 'f-'.$field['form_template_id'].'-'.$field['form_template_field_id'];
		echo '<dt class="'.$class.'"><label for="'.$field['name'].'">'.$field['text'].'</label></dt>';
		echo '<dd class="'.$class.' textfield"><textarea name="'.$field['name'].'" id="'.$field['name'].'" '.($field['required'] ? 'required' : '').'></textarea></dd>';
		 outputFieldOptionText($field, $options);
	}

	function outputRadioField($field){
		$options = json_decode($field['options']);
		$class = 'f-'.$field['form_template_id'].'-'.$field['form_template_field_id'];
		echo '<dt class="'.$class.'"><label for="'.$field['name'].'-0">'.$field['text'].'</label></dt>';
		echo '<dd class="'.$class.' radiofield">';
			echo '<ul>';
				$count = 0;
				foreach ($options->options as $key => $value) {
					echo '<li>';
					echo '<input type="radio" name="'.$field['name'].'" id="'.$field['name'].'-'.$count.'" value="'.$key.'" '.($field['required'] ? 'required' : '').'/>';
					echo '<label for="'.$field['name'].'-'.$count.'">'.$value.'</label>';
					echo '</li>';
					$count++;
				}
			echo '</ul>';
		echo '</dd>';
		 outputFieldOptionText($field, $options);
	}

	function outputCheckboxField($field){
		$options = json_decode($field['options']);
		$class = 'f-'.$field['form_template_id'].'-'.$field['form_template_field_id'];
		echo '<dt class="'.$class.'"><label for="'.$field['name'].'-0">'.$field['text'].'</label></dt>';
		echo '<dd class="'.$class.' checkboxfield">';
			echo '<ul>';
				$count = 0;
				foreach ($options->options as $key => $value) {
					echo '<li>';
					echo '<input type="checkbox" name="'.$field['name'].'[]" id="'.$field['name'].'-'.$count.'" value="'.$key.'" '.($field['required'] ? 'required' : '').' />';
					echo '<label for="'.$field['name'].'-'.$count.'">'.$value.'</label>';
					echo '</li>';
					$count++;
				}
			echo '</ul>';
		echo '</dd>';
		 outputFieldOptionText($field, $options);
	}

	function outputSelectField($field){
		$options = json_decode($field['options']);
		$class = 'f-'.$field['form_template_id'].'-'.$field['form_template_field_id'];
		echo '<dt class="'.$class.'"><label for="'.$field['name'].'">'.$field['text'].'</label></dt>';
		echo '<dd class="'.$class.' selectfield">';
			echo '<select name="'.$field['name'].'" id="'.$field['name'].'" '.($field['required'] ? 'required' : '').'>';
				foreach ($options->options as $key => $value) {
					echo '<option value="'.$key.'">'.$value.'</option>';
				}
			echo '</select>';
		echo '</dd>';
		 outputFieldOptionText($field, $options);
	}

	function outputFileField($field){
		$options = json_decode($field['options']);
		$class = 'f-'.$field['form_template_id'].'-'.$field['form_template_field_id'];
		echo '<dt class="'.$class.'"><label for="'.$field['name'].'">'.$field['text'].'</label></dt>';
		echo '<dd class="'.$class.'"><input type="file" name="'.$field['name'].'" id="'.$field['name'].'" value="" '.($field['required'] ? 'required' : '').' /></dd>';
		 outputFieldOptionText($field, $options);
	}

	function outputHeadingField($field){
		$options = json_decode($field['options']);
		$class = 'f-'.$field['form_template_id'].'-'.$field['form_template_field_id'];
		echo '<dt class="'.$class.' heading"><span class="formheading">'.$field['text'].'</span></dt>';
		 outputFieldOptionText($field, $options);
	}

	function outputFieldOptionText($field, $options){
		$class = 'f-'.$field['form_template_id'].'-'.$field['form_template_field_id'];
		if(@$options->text)
			echo '<dd class="'.$class.' text"><p>'.nl2br($options->text).'</p></dd>';
	}
?>

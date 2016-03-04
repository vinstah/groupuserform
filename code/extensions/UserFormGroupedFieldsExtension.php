<?php

class UserFormGroupedFieldsExtension extends UserFormFieldEditorExtension {

	/**
	 * Gets the field editor, for adding and removing EditableFormFields.
	 *
	 * @return GridField
	 */
	public function getFieldEditorGrid() {
		Requirements::javascript(USERFORMS_DIR . '/javascript/FieldEditor.js');

		$fields = $this->owner->Fields();

		$this->createInitialFormStep(true);

		$editableColumns = new GridFieldEditableColumns();
		$fieldClasses = singleton('EditableFormField')->getEditableFieldClasses();
		$editableColumns->setDisplayFields(array(
			'ClassName' => function($record, $column, $grid) use ($fieldClasses) {
				if($record instanceof EditableFormField) {
					return $record->getInlineClassnameField($column, $fieldClasses);
				}
			},
			'Title' => function($record, $column, $grid) {
				if($record instanceof EditableFormField) {
					return $record->getInlineTitleField($column);
				}
			}
		));

		$config = GridFieldConfig::create()
			->addComponents(
				$editableColumns,
				new GridFieldAddGroupedFields('buttons-before-right'),
				new GridFieldButtonRow(),
				GridFieldAddClassesButton::create('EditableTextField')
					->setButtonName(_t('UserFormFieldEditorExtension.ADD_FIELD', 'Add Field'))
					->setButtonClass('ss-ui-action-constructive'),
				GridFieldAddClassesButton::create('EditableFormStep')
					->setButtonName(_t('UserFormFieldEditorExtension.ADD_PAGE_BREAK', 'Add Page Break')),
				GridFieldAddClassesButton::create(array('EditableFieldGroup', 'EditableFieldGroupEnd'))
					->setButtonName(_t('UserFormFieldEditorExtension.ADD_FIELD_GROUP', 'Add Field Group')),
				new GridFieldEditButton(),
				new GridFieldDeleteAction(),
				new GridFieldToolbarHeader(),
				new GridFieldOrderableRows('Sort'),
				new GridFieldDetailForm()
			);

		$fieldEditor = GridField::create(
			'Fields',
			_t('UserDefinedForm.FIELDS', 'Fields'),
			$fields,
			$config
		)->addExtraClass('uf-field-editor');

		return $fieldEditor;
	}

}

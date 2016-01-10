<?php

class UserFormGroupedFieldsExtension extends UserFormFieldEditorExtension {
	public function updateCMSFields(FieldList $fields) {

		$fieldEditor = $this->getFieldEditorGrid();
		// $fields->removeFieldFromTab('Root.FormFields','Fields');
		// $fields->findOrMakeTab('Root.FormFields');
		$fields->insertAfter(new Tab('FormFields', _t('UserFormFieldEditorExtension.FORMFIELDS', 'Form Fields')), 'Main');
		$fields->addFieldToTab('Root.FormFields', $fieldEditor);
		return $fields;
	}
	/**
	 * Gets the field editor, for adding and removing EditableFormFields.
	 *
	 * @return GridField
	 */
	public function getFieldEditorGrid() {
		Requirements::javascript(USERFORMS_DIR . '/javascript/FieldEditor.js');

		$fields = $this->owner->Fields();

		// $this->createInitialFormStep(true);
		//
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
		// if (Config::inst()->get('UserFormGroupedFieldsExtension', 'showAddGroup')) {
			// $addFieldAction = new GridFieldAddNewMultiClass('buttons-before-right');
			$addFieldAction = new GridFieldAddNewGroupedFields('buttons-before-right');
			$config = GridFieldConfig::create()
				->addComponent($addFieldAction);
		// }

		$config
			->addComponents(
				$editableColumns,
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
			'GroupedFields',
			_t('UserDefinedForm.FIELDS', 'Fields'),
			$fields,
			$config
		)->addExtraClass('uf-field-editor');

		// if (Config::inst()->get('UserFormFieldEditorExtension', 'showAddEdit')) {
			$addFieldAction->setTitle('Add Field Block');
			$fields = $addFieldAction->getClasses($fieldEditor);
			$fields = array_diff_key($fields, array_flip(array('EditableFormStep', 'EditableFieldGroup', 'EditableFieldGroupEnd')));
			asort($fields);
			$addFieldAction->setClasses($fields);
		// }

		return $fieldEditor;
	}
}

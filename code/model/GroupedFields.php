<?php

class GroupedFields extends DataObject {

	private static $db = array(
		'Title' => 'Varchar(255)'
 	);

	private static $many_many = array(
		'Fields' => 'EditableFormField'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fieldEditor = $this->getFieldEditorGrid();
		$fields->removeByName('Fields');
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

		$fields = $this->Fields();

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
			->addComponent($editableColumns);

		if (Config::inst()->get('UserFormFieldEditorExtension', 'showAddEdit')) {
			$addFieldAction = new GridFieldAddNewMultiClass('buttons-before-right');

			$config
				->addComponent($addFieldAction);
		}

		$config
			->addComponents(
				new GridFieldButtonRow(),
				GridFieldAddClassesButton::create('EditableFormField')
					->setButtonName('Add Field'),
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

			if (Config::inst()->get('UserFormFieldEditorExtension', 'showAddEdit')) {
			$addFieldAction->setTitle(_t('UserFormFieldEditorExtension.ADD_EDIT_FIELD', 'Add & Edit Field'));

			$fields = $addFieldAction->getClasses($fieldEditor);
			$fields = array_diff_key($fields, array_flip(array('EditableFormStep', 'EditableFieldGroup', 'EditableFieldGroupEnd')));
			asort($fields);
			$addFieldAction->setClasses($fields);
		}

		return $fieldEditor;
	}
}

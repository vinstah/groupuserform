<?php

class EditableFormFieldExtension extends DataExtension {
	private static $belongs_to_many = array(
		'Groups' => 'GroupedFields'
	);
}
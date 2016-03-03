<?php

class FieldsAdmin extends ModelAdmin {
	private static $managed_models = array(
		'GroupedFields'
	);

	private static $url_segment = 'grouped-fields';

	private static $menu_title = 'Field Groups';
}
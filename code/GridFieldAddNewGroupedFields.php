<?php
/**
 * A component which lets the user select from a list of classes to create a new record form.
 *
 * By default the list of classes that are createable is the grid field's model class, and any
 * subclasses. This can be customised using {@link setClasses()}.
 */
class GridFieldAddNewGroupedFields implements GridField_HTMLProvider, GridField_URLHandler {

	private static $allowed_actions = array(
		'handleAdd'
	);

	// Should we add an empty string to the add class dropdown?
	private static $showEmptyString = true;

	private $fragment;

	private $title;

	private $classes;

	private $defaultClass;

	private $groupedFieldsJson;

	/**
	 * @var string
	 */
	protected $itemRequestClass = 'GridFieldAddNewMultiClassHandler';

	/**
	 * @param string $fragment the fragment to render the button in
	 */
	public function __construct($fragment = 'before') {
		$this->setFragment($fragment);
		$this->setTitle(_t('GridFieldExtensions.ADD', 'Add'));

		if(file_exists(GROUPEDFIELDS_DIR . '/groupedFields.json')) {
			$this->groupedFieldsJson = file_get_contents(GROUPEDFIELDS_DIR . '/groupedFields.json');
		}
	}

	/**
	 * Gets the fragment name this button is rendered into.
	 *
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment;
	}

	/**
	 * Sets the fragment name this button is rendered into.
	 *
	 * @param string $fragment
	 * @return GridFieldAddNewMultiClass $this
	 */
	public function setFragment($fragment) {
		$this->fragment = $fragment;
		return $this;
	}

	/**
	 * Gets the button title text.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the button title text.
	 *
	 * @param string $title
	 * @return GridFieldAddNewMultiClass $this
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Gets the classes that can be created using this button, defaulting to the model class and
	 * its subclasses.
	 *
	 * @param GridField $grid
	 * @return array a map of class name to title
	 */
	public function getClasses(GridField $grid) {
		return $this->getGroups();
	}

	/**
	 * Sets the classes that can be created using this button.
	 *
	 * @param array $classes a set of class names, optionally mapped to titles
	 * @return GridFieldAddNewMultiClass $this
	 */
	public function setClasses(array $classes, $default = null) {
		$this->classes = $classes;
		if($default) $this->defaultClass = $default;
		return $this;
	}

	/**
	 * Sets the default class that is selected automatically.
	 *
	 * @param string $default the class name to use as default
	 * @return GridFieldAddNewMultiClass $this
	 */
	public function setDefaultClass($default) {
		$this->defaultClass = $default;
		return $this;
	}

	/**
	 * Handles adding a new instance of a selected class.
	 *
	 * @param GridField $grid
	 * @param SS_HTTPRequest $request
	 * @return GridFieldAddNewMultiClassHandler
	 */

	public function handleAdd($grid, $request) {
		$group = $request->param('ClassName');
		$groupedFields = $this->findGroupedFieldsByID($group);
		if(empty($groupedFields)) {
			throw new SS_HTTPResponse_Exception(400);
		}
		// var_dump($groupedFields);
		$classes = array_values(ClassInfo::subclassesFor('EditableFormField'));
		// Add item to gridfield
		$list = $grid->getList();
		foreach($groupedFields as $field){
			$item = $field['type']::create();
			$item->Title = $field['title'];
			$item->write();
			$list->add($item);
		}


		// Should trigger a simple reload
		return Controller::curr()->redirectBack();
	}

	/**
	 * Find the group by requested group ID in groupedFieldsJson
	 * @param (string) $groupID
	 * @return array
	 *
	 */

	 public function findGroupedFieldsByID($groupID) {
		$questionsArray = array();
		if(array_key_exists($groupID, $this->getGroups())) {
			$json = json_decode($this->groupedFieldsJson, true);
	 		if(is_array($json)) {

	 			foreach($json['groups'] as $key => $group) {
					if($group['id'] == $groupID) {
						$questionsArray = $group['questions'];
					}
				}

				$questionFields = array();
				foreach($questionsArray as $k => $question) {
					$questionFields[] = $question['type'];
				}

				// return $questionFields;
	 		}
		}
		return $questionsArray;

	 }
	 /**
	  * Get groups for dropdown list
	  *
	  */
	 public function getGroups() {
 		$json = json_decode($this->groupedFieldsJson, true);
 		$groups = array();
  		if(is_array($json)) {
 			foreach($json['groups'] as $key => $group){
 				$groups[$group['id']] = $group['title'];
 			}
 		}
 		return $groups;
 	}

	/**
	 * {@inheritDoc}
	 */
	public function getHTMLFragments($grid) {
		$classes = $this->getClasses($grid);

		if(!count($classes)) {
			return array();
		}

		GridFieldExtensions::include_requirements();

		$field = new DropdownField(sprintf('%s[ClassName]', __CLASS__), '', $classes, $this->defaultClass);
		if (Config::inst()->get('GridFieldAddNewMultiClass', 'showEmptyString')) {
			$field->setEmptyString(_t('GridFieldExtensions.SELECTTYPETOCREATE', '(Select type to create)'));
		}
		$field->addExtraClass('no-change-track');

		$data = new ArrayData(array(
			'Title'      => $this->getTitle(),
			'Link'       => Controller::join_links($grid->Link(), 'add-multi-class', '{class}'),
			'ClassField' => $field
		));

		return array(
			$this->getFragment() => $data->renderWith('GridFieldAddNewMultiClass')
		);
	}



	/**
	 * {@inheritDoc}
	 */
	public function getURLHandlers($grid) {
		return array(
			'add-multi-class/$ClassName!' => 'handleAdd'
		);
	}

	public function setItemRequestClass($class) {
	  $this->itemRequestClass = $class;
	  return $this;
	}
}

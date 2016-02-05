<?php
/**
 * A component which lets the user select from a list of classes to create a new record form.
 *
 * By default the list of classes that are createable is the grid field's model class, and any
 * subclasses. This can be customised using {@link setClasses()}.
 */
class GridFieldAddGroupedFields implements GridField_HTMLProvider, GridField_URLHandler {

	private static $allowed_actions = array(
		'handleAdd'
	);

	// Should we add an empty string to the add class dropdown?
	private static $showEmptyString = true;

	private $fragment;

	private $title;

	private $classes;

	private $defaultClass;

	/**
	 * @var string
	 */
	protected $itemRequestClass = 'GridFieldAddGroupedFieldsHandler';

	/**
	 * @param string $fragment the fragment to render the button in
	 */
	public function __construct($fragment = 'before') {
		$this->setFragment($fragment);
		$this->setTitle(_t('GridFieldExtensions.ADD', 'Add'));
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
	 * Loads all the groups from GroupedFields
	 * @uses GroupedFields
	 * @return array fields mapped as ClassName => Title
	 */
	public function getGroups(){
		return GroupedFields::get()->map();
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

		$class     = $request->param('ClassName');
		$classes   = $this->getClasses($grid);
		$component = $grid->getConfig()->getComponentByType('GridFieldDetailForm');

		$addGroup = $request->param('ClassName');
		$group = GroupedFields::get()->filter('ID', $addGroup);
		if($group->exists()) {
			$groupedFields = $group->First()->Fields();
			if($groupedFields->exists()){
				$list = $grid->getList();

				foreach($groupedFields as $field) {
					$class = $field->ClassName;
					$item = $class::create();
					$item->Title = $field->Title;
					$item->write();
					$list->add($field);
				}
			}
		// Should trigger a simple reload
		return Controller::curr()->redirectBack();

		}
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
			'Link'       => Controller::join_links($grid->Link(), 'add-grouped-fields', '{class}'),
			'ClassField' => $field
		));

		return array(
			$this->getFragment() => $data->renderWith(__CLASS__)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getURLHandlers($grid) {
		return array(
			'add-grouped-fields/$ClassName!' => 'handleAdd'
		);
	}

	public function setItemRequestClass($class) {
	  $this->itemRequestClass = $class;
	  return $this;
	}
}

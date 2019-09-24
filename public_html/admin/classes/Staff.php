<?php

	use Configuration\AdminNavItem;
	use Configuration\AdminNavItemGenerator;
	use Configuration\Registry;
	use DatabaseObject\FormElement\ImageElement;
	use DatabaseObject\Generator;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\Column;

	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\Textarea;


	class Staff extends Generator implements AdminNavItemGenerator
	{
		const TABLE = "staff";

		const ID_FIELD = "staff_id";

		const SINGULAR = 'staff';
		const PLURAL = 'staff';

		/** @var	bool	Whether Testimonials use positioning */
		const HAS_POSITION = true;
		const HAS_ACTIVE = true;
		const LABEL_PROPERTY = 'name';

		const IMAGE_LOCATION = DOC_ROOT . "/resources/images/staff/";
		const IMAGE_WIDTH = STAFF_IMAGE_WIDTH;
		const IMAGE_HEIGHT = STAFF_IMAGE_HEIGHT;
		const IMAGE_SCALE_TYPE = ImageProperty::SCALE;

		public $name = "";
		public $bio = "";
		public $image = null;

		/**
		 * Sets the properties for a Testimonial
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new Property("name", "name", "string"));
			static::addProperty(new Property("bio", "bio", "string"));
			static::addProperty(new ImageProperty('image', 'image', static::IMAGE_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, static::IMAGE_SCALE_TYPE));
		}

		/**
		 * Sets the array of Columns that are displayed to the user for this object type
		 */
		 protected static function columns()
		{
			static::addColumn(new Column('Staff Profiles', 'name'));

			parent::columns();
		}

		/**
		 * Gets all the active testimonials
		 * @return	static[]	Said testimonials
		 */
		public static function loadAllActive()
		{
			return static::loadAllFor("active", true, ["position" => true]);
		}

		/**
		 * Gets a random Testimonial
		 * @return	Testimonial		A random Testimonial
		 */
		// public static function loadRandom()
		// {
		// 	$query = "SELECT ~PROPERTIES "
		// 		   . "FROM ~TABLE "
		// 		   . "ORDER BY RAND() "
		// 		   . "LIMIT 1";
		//
		// 	return static::makeOne($query);
		// }

		/**
		 * Sets the Form Elements for this object
		 */
		 protected function formElements()

		{
			parent::formElements();

			$this->addFormElement(new Text('name', 'Name'));
			$this->addFormElement(new Textarea('bio', 'Bio'));
			$this->addFormElement(new ImageElement("image", 'Image', $this->image));

		}

		//region AdminNavItemGenerator

		/**
		 * Gets the nav item for this class
		 * @return    AdminNavItem        The admin nav item for this class
		 */
		public static function getAdminNavItem()
		{
			return new AdminNavItem(static::getAdminNavLink(), "Staff", [static::class], Registry::isEnabled("Staff"));
		}

		//endregion
	}

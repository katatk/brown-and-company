<?php
	namespace Pages\Home;

	use Pages\Page;

  use DatabaseObject\FormElement\Editor;
  use DatabaseObject\FormElement\Text;
  use DatabaseObject\Property\Property;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\FormElement\ImageElement;
	use DatabaseObject\FormElement\Textarea;

	use Files\Image;

	class HomePage extends Page
	{

		const TABLE = "home_page";

		public $image_1 = null;
		public $banner_title = '';
		public $banner_text = '';
		public $banner_button = '';

		public $blue_1_link = '';
		public $blue_1_title = '';
		public $blue_1_text = '';

		public $blue_2_link = '';
		public $blue_2_title = '';
		public $blue_2_text = '';

		public $blue_3_link = '';
		public $blue_3_title = '';
		public $blue_3_text = '';

		public $blue_4_link = '';
		public $blue_4_title = '';
		public $blue_4_text = '';

		public $image_2 = null;
		public $image_3 = null;

		const IMAGE_LOCATION = DOC_ROOT . "/resources/images/page/";
		const IMAGE_WIDTH = PAGE_AUX_WIDTH;
		const IMAGE_HEIGHT = PAGE_AUX_HEIGHT;


		/**
		 * Sets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new ImageProperty('image_1', 'image_1', static::IMAGE_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT), static::TABLE);
			static::addProperty(new Property("banner_title", "banner_title", "string"));
			static::addProperty(new Property("banner_text", "banner_text", "html"));
			static::addProperty(new Property("banner_button", "banner_button", "string"));

			static::addProperty(new Property("blue_1_link", "blue_1_link", "string"));
			static::addProperty(new Property("blue_1_title", "blue_1_title", "string"));
			static::addProperty(new Property("blue_1_text", "blue_1_text", "html"));
			static::addProperty(new Property("blue_2_title", "blue_2_title", "string"));
			static::addProperty(new Property("blue_2_link", "blue_2_link", "html"));
			static::addProperty(new Property("blue_2_text", "blue_2_text", "html"));
			static::addProperty(new Property("blue_3_link", "blue_3_link", "string"));
			static::addProperty(new Property("blue_3_title", "blue_3_title", "string"));
			static::addProperty(new Property("blue_3_text", "blue_3_text", "html"));
			static::addProperty(new Property("blue_4_link", "blue_4_link", "string"));
			static::addProperty(new Property("blue_4_title", "blue_4_title", "string"));
			static::addProperty(new Property("blue_4_text", "blue_4_text", "html"));

			static::addProperty(new ImageProperty('image_2', 'image_2', static::IMAGE_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT), static::TABLE);		static::addProperty(new ImageProperty('image_3', 'image_3', static::IMAGE_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT), static::TABLE);
		}

		/**
		 * Sets the Form Elements for this Entity
		 */
		protected function formElements()
		{
			parent::formElements();

			$this->addFormElement(new ImageElement('image_1', 'First Image'), "Content");
			$this->addFormElement(new Text("banner_title", "Banner Title"), "Home Banner");
			$this->addFormElement(new Editor("banner_text", "Banner Text"), "Home Banner");
			$this->addFormElement(new Text("banner_button", "Banner Button"), "Home Banner");

			$this->addFormElement(new Text("blue_1_link", "Blue Column 1 Link"), "Content");
			$this->addFormElement(new Text("blue_1_title", "Blue Column 1 Title"), "Content");
			$this->addFormElement(new Textarea("blue_1_text", "Blue Column 1 Text"), "Content");

			$this->addFormElement(new Text("blue_2_link", "Blue Column 2 Link"), "Content");
			$this->addFormElement(new Text("blue_2_title", "Blue Column 2 Title"), "Content");
			$this->addFormElement(new Textarea("blue_2_text", "Blue Column 2 Text"), "Content");

			$this->addFormElement(new Text("blue_3_link", "Blue Column 3 Link"), "Content");
			$this->addFormElement(new Text("blue_3_title", "Blue Column 3 Title"), "Content");
			$this->addFormElement(new Textarea("blue_3_text", "Blue Column 3 Text"), "Content");

			$this->addFormElement(new Text("blue_4_link", "Blue Column 4 Link"), "Content");
			$this->addFormElement(new Text("blue_4_title", "Blue Column 4 Title"), "Content");
			$this->addFormElement(new Textarea("blue_4_text", "Blue Column 4 Text"), "Content");

			$this->addFormElement(new ImageElement('image_2', 'Second Image'), "Content");	$this->addFormElement(new ImageElement('image_3', 'Third Image'), "Content");

		}
	}

<?php
	namespace Pages\Home;

	use Pages\Page;
	use Pages\ContentPage;

  use DatabaseObject\FormElement\Editor;
  use DatabaseObject\FormElement\Text;
  use DatabaseObject\Property\Property;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\FormElement\ImageElement;
	use DatabaseObject\FormElement\Textarea;
	use DatabaseObject\FormElement\Checkbox;
	use DatabaseObject\FormElement\GridElement;

	use Files\Image;

	class HomePage extends ContentPage
	{

		const TABLE = "home_page";

		const SLIDE_CLASS = HomeSlide::class;


		public $image_1 = null;

		public $second_section_title = '';
		public $second_section_text = '';
		public $second_section_button = '';
		public $second_section_button_link = '';

		public $thrid_section_title = '';
		public $thrid_section_text = '';
		public $thrid_section_button = '';
		public $thrid_section_button_link = '';


		/**
		 * Sets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();




			static::addProperty(new Property("second_section_title", "second_section_title", "string"));
			static::addProperty(new Property("second_section_text", "second_section_text", "html"));
			static::addProperty(new Property("second_section_button", "second_section_button", "string"));
			static::addProperty(new Property("second_section_button_link", "second_section_button_link", "string"));

			static::addProperty(new Property("third_section_title", "third_section_title", "string"));
			static::addProperty(new Property("third_section_text", "third_section_text", "html"));
			static::addProperty(new Property("third_section_button", "third_section_button", "string"));
			static::addProperty(new Property("third_section_button_link", "third_section_button_link", "string"));


		}

		/**
		 * Sets the Form Elements for this Entity
		 */
		protected function formElements()
		{
			parent::formElements();

			if(PAGE_HAS_SLIDESHOW)
			{
				$this->addFormElement(new Checkbox("useSlideshow", 'Display slideshow'), "Slideshow");

				/** @var ImageProperty $imageProperty */
				$this->addFormElement(new GridElement('slides', 'Slides'), "Slideshow");
			}

			// $this->addFormElement(new Text("banner_title", "Banner Title"), "Home Banner");
			// $this->addFormElement(new Editor("banner_text", "Banner Text"), "Home Banner");
			// $this->addFormElement(new Text("banner_button", "Banner Button"), "Home Banner");

			$this->addFormElement(new Text("second_section_title", "Second Section Title"), "Content");
			$this->addFormElement(new Editor("second_section_text", "Second Section Text"), "Content");
			$this->addFormElement(new Text("second_section_button", "Second Section Button"), "Content");
			$this->addFormElement(new Text("second_section_button_link", "Second Section Button Link"), "Content");


			$this->addFormElement(new Text("third_section_title", "Third Section Title"), "Content");
			$this->addFormElement(new Editor("third_section_text", "Third Section Text"), "Content");
			$this->addFormElement(new Text("third_section_button_link", "Third Section Button Link"), "Content");


		}
	}

<?php
	namespace Pages;

	use Pages\Page;

  use DatabaseObject\FormElement\Editor;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\FormElement\ImageElement;
	use Files\Image;
	use DatabaseObject\Property\Property;

	class ServicesPage extends Page
	{

		const TABLE = "services_page";

		public $textOne = '';
		public $imageOne = null;
		public $textTwo = '';
		public $imageTwo = null;

		const IMAGE_LOCATION = DOC_ROOT . "/resources/images/page/";
		const IMAGE_WIDTH = PAGE_AUX_WIDTH;
		const IMAGE_HEIGHT = PAGE_AUX_HEIGHT;

		/**
		 * Sets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new Property("textOne", "text_one", "html"));
			static::addProperty(new ImageProperty('imageOne', 'image_one', static::IMAGE_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT), static::TABLE);
			static::addProperty(new Property("textTwo", "text_two", "html"));
			static::addProperty(new ImageProperty('imageTwo', 'image_two', static::IMAGE_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT), static::TABLE);
		}

		/**
		 * Sets the Form Elements for this Entity
		 */
		protected function formElements()
		{
			parent::formElements();

			$this->addFormElement(new Editor("textOne", "Text Area One"), "Content");
			$this->addFormElement(new ImageElement('imageOne', 'Image One'), "Content");
			$this->addFormElement(new Editor("textTwo", "Text Area Two"), "Content");
			$this->addFormElement(new ImageElement('imageTwo', 'Image Two'), "Content");


		}
	}

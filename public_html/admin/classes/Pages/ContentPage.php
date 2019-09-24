<?php
	namespace Pages;

	use Pages\Page;

  use DatabaseObject\FormElement\Text;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\FormElement\ImageElement;
	use Files\Image;
	use DatabaseObject\Property\Property;

	class ContentPage extends Page
	{

		const TABLE = "content_page";


		public $buttonText = '';
		public $auxImage = null;

		const IMAGE_LOCATION = DOC_ROOT . "/resources/images/page/";
		const IMAGE_WIDTH = PAGE_AUX_WIDTH;
		const IMAGE_HEIGHT = PAGE_AUX_HEIGHT;

		/**
		 * Sets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new Property("buttonText", "button_text", "string"));
			static::addProperty(new ImageProperty('auxImage', 'image', static::IMAGE_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT), static::TABLE);
		}

		/**
		 * Sets the Form Elements for this Entity
		 */
		protected function formElements()
		{
			parent::formElements();

			$this->addFormElement(new Text("buttonText", "Button Text"), "Content");
			$this->addFormElement(new ImageElement('auxImage', 'Image'), "Content");


		}
	}

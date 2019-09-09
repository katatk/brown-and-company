<?php
	namespace Pages;

	use Forms\Form;

	/**
	 * Displays a normal page to the user
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	class ContactPageController extends PageController
	{

		/**
		 * Sets the variables that the template has access to
		 * @return	array	An array of [string => mixed] variables that the template has access to
		 */
		protected function getTemplateVariables()
		{
			$variables = parent::getTemplateVariables();

			$variables["contactForm"] = Form::load(Form::CONTACT_ID);

			return $variables;
		}
	}

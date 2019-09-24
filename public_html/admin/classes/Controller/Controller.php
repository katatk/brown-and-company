<?php
	namespace Controller;

	use Cart\Cart;
	use Configuration\Configuration;
	use Users\User;
	use Assets\Script;
	use Pages\Page;

	/**
	 * Handles displaying content to the user
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	abstract class Controller
	{
		/**
		 * Retrieves the location of the template to display to the user
		 * @return	string	The location of the template
		 */
		abstract protected function getTemplateLocation();

		/**
		 * Sets the variables that the template has access to
		 * @return	array	An array of [string => mixed] variables that the template has access to
		 */
		protected function getTemplateVariables()
		{
			$user = User::get();

			return
			[
				"_get" => $_GET,
				"_post" => $_POST,
				"cart" => Cart::get(),
				"config" => Configuration::acquire(),
				"controller" => $this,
				"slides" => [],
				"templateDir" => DOC_ROOT . "/theme",
				"user" => $user,
				"homePath" => Page::getHomepagePath(),
				"isPopup" => isset($_GET["popup"]),
				
				"navItems" => Page::loadAllForMultiple(
				[
					"active" => true,
					"onNav" => true,
					"parent" => null
				]),
				
				"script" => new Script //for javascript includes in twig
			];
		}

		/**
		 * Sets the template variables and loads the template
		 */
		public function output()
		{
			echo Twig::render($this->getTemplateLocation(), $this->getTemplateVariables());
		}

		/**
		 * Handy function to be able to call static methods from twig (twig includes the controller as a variable)
		 *
		 * @param $className 	The namespaced name of the class which has the method
		 * @param $methodName 	What to call
		 * @param $params 		an array of any parameters the static method might need
		 */
		public function callStatic($className, $methodName, ...$params)
		{
			return call_user_func_array([$className, $methodName], $params);
		}

		/**
		 * Handy function to be able to get static constants from twig (twig includes the controller as a variable)
		 *
		 * @param $className 	The namespaced name of the class which has the constant
		 * @param $constName 	What constant to get
		 */
		public function getStaticConst($className, $constName)
		{
			return constant($className . '::' . $constName);
		}
	}

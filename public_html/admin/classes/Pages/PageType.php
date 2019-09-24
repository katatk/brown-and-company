<?php
	namespace Pages;

	// use Blog\BlogController;
	// use Blog\BlogPage;
	use Configuration\Registry;
	use Pages\Page;
	use Pages\ContentPage;
	use Pages\ServicesPage;
	use Pages\Home\HomePage;
	use Pages\Home\HomeController;
	use Projects\ProjectsController;
	use Projects\ProjectsPage;
	use Pages\FrontPage\FrontPageController;
	// use Products\ProductsController;
	// use Products\ProductsPage;
	// use Payments\BillPaymentController;

	/**
	 * Keeps track of the various page types
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	class PageType
	{
		/** @var string|Page */
		public $class = Page::class;
		public $template = "page";

		/** @var string|PageController */
		public $controller = PageController::class;

		/**
		 * Gets the page types
		 * @return	self[]	The page types
		 */
		public static function get()
		{
			$types =
				[
					"Page" => new static()
				];

				$types["Contact"] = new static(Page::class, "general/contact-page", ContactPageController::class);

				// custom page types
				$types["Home"] = new static(HomePage::class, "general/home-page", HomeController::class);

				$types["Page with Button"] = new static(ContentPage::class, "general/page-with-button");

				$types["Staff"] = new static(Page::class, "general/staff-page");

				$types["Services"] = new static(ServicesPage::class, "general/services-page");

			if(Registry::isEnabled("Projects"))
			{
				$types["Projects"] = static::createWithController(Page::class, ProjectsController::class);
			}

			if(Registry::isEnabled("FAQs"))
			{
				$types["FAQs"] = new static(FaqPage::class, "general/faq-page");
			}

			if(Registry::isEnabled("Testimonials"))
			{
				$types["Testimonials"] = new static(Page::class, "general/testimonials-page");
			}

			if(Registry::isEnabled("Products"))
			{
				$types["Products"] = static::createWithController(Page::class, ProductsController::class);
			}

			if(Registry::isEnabled("Payments"))
			{
				$types["Payments"] = new static(Page::class, "payments/payments-page", BillPaymentController::class);
			}



			return $types;
		}

		/**
		 * Creates a new page type
		 * @param	string	$class			The page's class
		 * @param	string	$template		The page's template
		 * @param	string	$controller		The page's controller
		 */
		private function __construct($class = Page::class, $template = "general/page", $controller = PageController::class)
		{
			$this->class = $class;
			$this->template = $template . '.twig';
			$this->controller = $controller;
		}

		/**
		 * A controller only option for when you have page types defined in the controller
		 * @param	string	$class			The page's class
		 * @param	string	$controller		The page's controller
		 */
		private static function createWithController($class = Page::class, $controller = PageController::class)
		{
			$type = new static($class);
			$type->controller = $controller;
			return $type;
		}
	}

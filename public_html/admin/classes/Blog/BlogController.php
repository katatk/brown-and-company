<?php
	namespace Blog;

	use Controller\UrlController;
	use Pages\Page;
	use Pages\PageController;

	/**
	 * A Blog Controller handles displaying Blog Articles
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	class BlogController extends PageController
	{
		private $article;

		/**
		 * Retrieves the child patterns that can belong to this controller
		 * @return	PageController[]|string[]	Pattern to controller class names, example: ['/$category/' => CategoryController::class, '/$category/$product/' => ProductsController::class]
		 */
		protected static function getChildPatterns()
		{
			return ['/$article/' => self::class];
		}

		/**
		 * Retrieves a Page Child Controller that matches a pattern, or returns null otherwise
		 * @param	UrlController	$parent		The parent to the Page Child Controller
		 * @param	string[]		$matches	An array of name to string values, so a pattern '/$category/$product/$size/' matching "/pets/dog/small/" would give ["category" => "pets", "product" => "dog", "size" => "small"]
		 * @param	string			$pattern	The pattern that was matched
		 * @return	self						An object of this type, or null if one can't be found
		 */
		protected static function getControllerFromPattern(UrlController $parent = null, array $matches = [], $pattern = "")
		{
			/** @var PageController $parent */
			$article = BlogArticle::loadForSlug($matches["article"]);

			if($article->isNull())
			{
				return null;
			}

			return new static($parent->page, $article);
		}

		/**
		 * Creates a new Blog Controller object
		 * @param	Page			$page			The page to display
		 * @param	BlogArticle		$article		The article to display
		 */
		public function __construct(Page $page, BlogArticle $article = null)
		{
			parent::__construct($page);

			$this->article = $article;
		}

		/**
		 * Retrieves the location of the template to display to the user
		 * @return	string	The location of the template
		 */
		 protected function getTemplateLocation()
 		{
 			if($this->article !== null)
 			{
 				return "blog/article-page.twig";
 			}
 			else
 			{
 				return "blog/blog-page.twig";
 			}
 		}

		/**
		 * Sets the variables that the template has access to
		 * @return	array	An array of [string => mixed] variables that the template has access to
		 */
		 protected function getTemplateVariables()
 		{
 			$variables = parent::getTemplateVariables();

 			if($this->article !== null)
 			{
 				$variables["article"] = $this->article;
 			}
 			else
 			{
 				$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
 				$variables["currentPage"] = $currentPage;
 				$variables["articles"] = BlogArticle::loadPagesForMultiple(['active' => true], ['date' => false, 'id' => false], $currentPage, ARTICLES_PER_PAGE);
 			}

 			return $variables;
 		}
	}

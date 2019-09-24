<?php
	namespace Projects;

	use Controller\UrlController;
	use Pages\Page;
	use Pages\PageController;

	/**
	 * A Project Controller handles displaying Project s
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	class ProjectsController extends PageController
	{
		private $project;

		/**
		 * Retrieves the child patterns that can belong to this controller
		 * @return	PageController[]|string[]	Pattern to controller class names, example: ['/$category/' => CategoryController::class, '/$category/$product/' => ProductsController::class]
		 */
		protected static function getChildPatterns()
		{
			return ['/$project/' => self::class];
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
			$project = Project::loadForSlug($matches["project"]);

			if($project->isNull())
			{
				return null;
			}

			return new static($parent->page, $project);
		}

		/**
		 * Creates a new Project Controller object
		 * @param	Page			$page			The page to display
		 * @param	Project		$project		The project to display
		 */
		public function __construct(Page $page, Project $project = null)
		{
			parent::__construct($page);

			$this->project = $project;
		}



		/**
		 * Retrieves the location of the template to display to the user
		 * @return	string	The location of the template
		 */
		 protected function getTemplateLocation()
 		{

			// if there is a single project, get individual project template, else get projects page template
			if($this->project !== null)
			{
				return "projects/project.twig";
			}
			else
			{
				return "projects/projects-page.twig";
			}

 		}

		/**
		 * Sets the variables that the template has access to
		 * @return	array	An array of [string => mixed] variables that the template has access to
		 */
		 protected function getTemplateVariables()
 		{
 			$variables = parent::getTemplateVariables();

 			if($this->project !== null)
 			{
 				$variables["project"] = $this->project;
				$variables["slides"] = [$this->project];
				// $variables["page"]->bannerImage = $this->project->banner;
				$variables["page"]->title = $this->project->title . " - " . SITE_FROM_NAME;
				$variables["page"]->description = $this->project->summary;
 			}
 			else
 			{
 				$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
 				$variables["currentPage"] = $currentPage;
 				$variables["projects"] = Project::loadAllFor("active", true);
 			}

 			return $variables;
 		}

	}

<?php
	namespace Pages\Home;

	use Pages\PageController;
	use Blog\BlogArticle;
	use Projects\Project;
	use Galleries\Gallery;

	/**
	 * A Home Controller loads anything extra needed for the front page
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	class HomeController extends PageController
	{
		/**
		 * Sets the variables that the template has access to
		 * @return	array	An array of [string => mixed] variables that the template has access to
		 */
		protected function getTemplateVariables()
		{
			$variables = parent::getTemplateVariables();

			// module content which displays on the homepage eg
			// $variables['recentArticles'] = BlogArticle::getRecent(2);
				$variables["projects"] = Project::loadAllFor("active", true);
				$variables["homeGallery"] = Gallery::load(Gallery::HOME_GALLERY_ID);

			return $variables;
		}
	}

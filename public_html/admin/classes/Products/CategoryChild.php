<?php

namespace Products;

use DatabaseObject\ParentChild;

/**
 * Handles the possible children for a Category
 * @author	Callum Muir <callum@activatedesign.co.nz>
 */
class CategoryChild extends ParentChild
{
	const PARENT_PROPERTY = 'parent';

	protected static $groupMembers = [Category::class, ProductCategory::class];
}

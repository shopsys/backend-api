<?php

namespace Shopsys\ShopBundle\Component\Transformers;

use Shopsys\ShopBundle\Model\Category\CategoryFacade;
use Symfony\Component\Form\DataTransformerInterface;

class CategoriesTypeTransformer implements DataTransformerInterface
{
    /**
     * @var \Shopsys\ShopBundle\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @param \Shopsys\ShopBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(CategoryFacade $categoryFacade)
    {
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Category\Category[]|null $categories
     * @return bool[]
     */
    public function transform($categories)
    {
        $categories = $categories ?? [];
        $allCategories = $this->categoryFacade->getAllCategoriesOfCollapsedTree($categories);

        $isCheckedIndexedByCategoryId = [];
        foreach ($allCategories as $category) {
            $isChecked = in_array($category, $categories, true);
            $isCheckedIndexedByCategoryId[$category->getId()] = $isChecked;
        }

        return $isCheckedIndexedByCategoryId;
    }

    /**
     * @param bool[]|null $isCheckedIndexedByCategoryId
     * @return \Shopsys\ShopBundle\Model\Category\Category[]
     */
    public function reverseTransform($isCheckedIndexedByCategoryId)
    {
        $categories = [];
        foreach ($isCheckedIndexedByCategoryId ?? [] as $categoryId => $isChecked) {
            if ($isChecked) {
                $categories[] = $this->categoryFacade->getById($categoryId);
            }
        }

        return $categories;
    }
}
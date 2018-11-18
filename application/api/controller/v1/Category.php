<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/28
 * Time: 15:20
 */

namespace app\api\controller\v1;

use app\api\model\Category as CategoryModel;
use app\lib\exception\CategoryException;

class Category
{
    public function getAllCategories(){
        $category=CategoryModel::with('image')->select();
        if($category->isEmpty()){
            throw new CategoryException();
        }
        return $category;
    }
}
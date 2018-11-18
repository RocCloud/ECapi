<?php

namespace app\api\controller\v1;

use app\api\validate\IDCollection;
use app\api\model\Theme as ThemeModel;
use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\ThemeException;

class Theme
{
    /**
     * @url /theme?ids=id1,id2,id3,...
     * @return  一组theme模型
     */
    public function getSimpleList($ids=''){
        (new IDCollection())->goCheck();
        $ids=explode(',',$ids);
        $theme=ThemeModel::with('topicImg,headImg')->select($ids);
        if($theme->isEmpty()){
            throw new ThemeException();
        }
        return $theme;
    }

    /**
     * @url /theme/:id
     * @return  一组theme及其下商品信息
     */
    public function getComplexOne($id){
        (new IDMustBePostiveInt())->goCheck();
        $theme=ThemeModel::getThemeWithProduct($id);
        if(!$theme){
            throw new ThemeException();
        }
        return $theme;
    }
}

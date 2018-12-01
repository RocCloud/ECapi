<?php

use think\Route;

//Route::rule('路由表达式','路由地址','请求类型','路由参数（数组）','变量规则（数组）');

Route::get('api/:versions/banner/:id','api/:versions.Banner/getBanner');

Route::group('api/:versions/theme',function (){
    Route::get('','api/:versions.Theme/getSimpleList');
    Route::get('/:id','api/:versions.Theme/getComplexOne');
});

Route::group('api/:versions/product',function (){
    Route::get('/by_category','api/:versions.Product/getAllInCategory');
    Route::get('/:id','api/:versions.Product/getOne',[],['id'=>'\d+']);
    Route::get('/recent','api/:versions.Product/getRecent');
});

Route::get('api/:versions/category/all','api/:versions.Category/getAllCategories');

Route::group('api/:versions/token',function (){
    Route::post('/user','api/:versions.Token/getToken');
    Route::post('/verify','api/:versions.Token/verifyToken');
    Route::post('/app','api/:versions.Token/getAppToken');
});

Route::group('api/:versions/address',function (){
    Route::post('','api/:versions.Address/createOrUpdateAddress');
    Route::get('','api/:versions.Address/getUserAddress');
});

Route::group('api/:versions/order',function (){
    Route::post('','api/:versions.Order/placeOrder');
    Route::get('/:id','api/:versions.Order/getDetail',[],['id'=>'\d+']);
    Route::get('/by_user','api/:versions.Order/getSummaryByUser');
    Route::get('/paginate','api/:versions.Order/getSummary');
    Route::put('/delivery','api/:versions.Order/delivery');
    Route::get('/by_status','api/:versions.Order/getSummaryByStatus');
});

Route::group('api/:versions/pay',function (){
    Route::post('/pre_order','api/:versions.Pay/getPreOrder');
    Route::post('/notify','api/:versions.Pay/receiveNotify');
});
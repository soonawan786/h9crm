<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Product\StoreProductBrand;
use App\Models\BaseModel;
use App\Models\ProductBrand;

class ProductBrandController extends AccountBaseController
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->brands = ProductBrand::all();
        return view('products.brand.create', $this->data);
    }

    /**
     * @param StoreProductCategory $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreProductBrand $request)
    {
        $brand = new ProductBrand();
        $brand->brand_name = $request->brand_name;
        $brand->save();
        $brands = ProductBrand::get();
        $options = BaseModel::options($brands, $brand, 'brand_name');
        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    /**
     * @param StoreProductCategory $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreProductBrand $request, $id)
    {
        $brand = ProductBrand::findOrFail($id);
        $brand->brand_name = strip_tags($request->brand_name);
        $brand->save();

        $brands = ProductBrand::get();
        $options = BaseModel::options($brands, null, 'brand_name');

        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $options]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ProductBrand::destroy($id);
        $brandData = ProductBrand::all();
        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $brandData]);
    }

}

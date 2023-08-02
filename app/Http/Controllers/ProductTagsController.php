<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Product\StoreProductTags;
use App\Models\BaseModel;
use App\Models\ProductTags;

class ProductTagsController extends AccountBaseController
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->tags = ProductTags::all();
        return view('products.tag.create', $this->data);
    }

    /**
     * @param StoreProductCategory $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreProductTags $request)
    {
        $tag = new ProductTags();
        $tag->tag_name = $request->tag_name;
        $tag->save();
        $tags = ProductTags::get();
        $options = BaseModel::options($tags, $tag, 'tag_name');
        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    /**
     * @param StoreProductCategory $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreProductTags $request, $id)
    {
        $tag = ProductTags::findOrFail($id);
        $tag->tag_name = strip_tags($request->tag_name);
        $tag->save();

        $tags = ProductTags::get();
        $options = BaseModel::options($tags, null, 'tag_name');

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
        ProductTags::destroy($id);
        $tagData = ProductTags::all();
        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $tagData]);
    }

}

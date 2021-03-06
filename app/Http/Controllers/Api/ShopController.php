<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;


use App\Product;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as V;
use App\Catalog;
use App\Property;

class ShopController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $catalog;
    public function __construct (Catalog $catalog)
    {
        $this->catalog = $catalog;
    }

    public function list ()
    {
        $products = Product::with('catalogs')->with('properties')->get();

        /**
         * @var \Illuminate\View\View $view
         */
        $view = View::make('shop', ['products' => $products]);
        $view->nest('links', 'layouts.links');
        // return $view;
        return response()->json([
                  'products' => $products
                ]);
    }

    public function get_product ($id)
    {
        $product = Product::with('properties')->find($id);
//      return view('shop.single', ['product' => $product]);
        return response()->json([
                  'product' => $product
                ]);

    }

    public function get_child_catalogs ($id)
    {
        $child_catalogs = Catalog::where('parent_id', $id)->get();

        $catalog_ids = $this->catalog->get_catalog_ids_tree((int) $id);

        $products = Product::whereIn('catalog_id', $catalog_ids)->with('catalogs')->with('properties')->get();

        $parent_id = $id;
        $parent_catalogs_array = [];
        while ($parent_id !== NULL) {
            $parent_catalog = $this->catalog::find($parent_id);
            $parent_id = $parent_catalog->parent_id;
            $parent_catalogs_array[] = ['id' => $parent_catalog->id, 'name' => $parent_catalog->name];
        }
        $parent_catalogs_array = array_reverse($parent_catalogs_array);
        $properties = Property::with('propertyValues')->orderBy('priority')->get();

        /**
         * @var \Illuminate\View\View $view
         */
        $view = View::make('shop', ['products' => $products, 'catalogs' => $child_catalogs, 'parent_catalogs' => $parent_catalogs_array]);
        $view->nest('filter', 'layouts.filter', ['properties' => $properties]);


        return response()->json([
          'products' => $products,
          'catalogs' => $child_catalogs,
          'parent_catalogs' => $parent_catalogs_array
      ]);

        // return $view;
    }
}

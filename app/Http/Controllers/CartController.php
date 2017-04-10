<?php

namespace App\Http\Controllers;

use App\Cart\CartCollection;
use App\Cart\CashDraft;
use App\Cart\CreditDraft;
use App\Cart\Item;
use App\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private $cart;

    public function __construct()
    {
        $this->cart = new CartCollection();
    }

    public function index(Request $request)
    {
        $draft = $this->cart->content()->last();

        return view('cart.index', compact('draft'));
    }

    public function show(Request $request, $draftKey)
    {
        $queriedProducts = Product::where(function ($query) use ($request) {
            return $query->where('name', 'like', '%'.$request->get('query').'%');
        })->get();

        $draft = $this->cart->get($draftKey);

        return view('cart.index', compact('draft', 'queriedProducts'));
    }

    public function add(Request $request)
    {
        if ($request->has('create-cash-draft')) {
            $this->cart->add(new CashDraft());
        } else {
            $this->cart->add(new CreditDraft());
        }

        return redirect()->route('cart.show', $this->cart->content()->last()->draftKey);
    }

    public function addDraftItem(Request $request, $draftKey, Product $product)
    {
        $item = new Item($product, $request->qty);
        $this->cart->addItemToDraft($draftKey, $item);

        return redirect()->route('cart.index', $item->draftKey);
    }

    public function updateDraftItem(Request $request, $draftKey)
    {
        $this->cart->updateDraftItem($draftKey, $request->item_key, $request->only('qty', 'item_discount'));

        return redirect()->route('cart.index', $draftKey);
    }

    public function removeDraftItem(Request $request, $draftKey)
    {
        $this->cart->removeItemFromDraft($draftKey, $request->item_index);

        return redirect()->route('cart.index', $draftKey);
    }

    public function empty($draftKey)
    {
        $this->cart->emptyDraft($draftKey);

        return redirect()->route('cart.index', $draftKey);
    }

    public function remove(Request $request)
    {
        $this->cart->removeDraft($request->draft_key);

        return redirect()->route('cart.index');
    }

    public function destroy()
    {
        $this->cart->destroy();

        return redirect()->route('cart.index');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    public function home(Request $request) {
        $products = new Product();

        if (isset($request->field)) {
            $products = Product::orderBy($request->field, $request->state)->get();
        }

        if ($request->ajax()) {
            return view('ajax.order', ['products' => $products])->render();
        }
        //Если пользователь авторизован, подтягиваем корзину из базы, иначе из сессии
        if (Auth::check()) {
            $id = Auth::id();
            $cart = Cart::where('user_id', $id)->get();
            if (count($cart) == 0) {
                //Создаем новую корзину для пользователя
                $cart = new Cart();
                $cart->user_id = $id;
                $cart->sum = 0;
                $cart->save();
            } else {
                $cart = $cart[0];
            }
            $data['sum'] = $cart->sum;
            $data['count'] = $cart->products()->count();
            $data['cartProducts'] = [];
            foreach($cart->products as $key) {
                $data['cartProducts'][] = $key->id;
            }
        } else {
            //Проверяем наличие переменных в сессии
            $data['sum'] = session('sum');
            if (is_null($data['sum'])) {
                $data['sum'] = 0;
                session(['sum' => $data['sum']]);
            }
            $data['count'] = session('count');
            if (is_null($data['count'])) {
                $data['count'] = 0;
                session(['count' => $data['count']]);
            }
            $data['cartProducts'] = session('cartProducts');
            if (is_null($data['cartProducts'])) {
                $data['cartProducts'] = [];
                session(['cartProducts' => $data['cartProducts']]);
            }
        }



        return view('home', ['products' => $products->all(), 'data' => $data]);
    }

    public function cart() {
        //Если пользователь авторизован, подтягиваем корзину из базы, иначе из сессии
        if (Auth::check()) {
            $id = Auth::id();
            $cart = Cart::where('user_id', $id)->get();
            if (count($cart) == 0) {
                //Создаем новую корзину для пользователя
                $cart = new Cart();
                $cart->user_id = $id;
                $cart->sum = 0;
                $cart->save();
            } else {
                $cart = $cart[0];
            }
            $data['sum'] = $cart->sum;
            $data['count'] = $cart->products()->count();
            $data['cartProducts'] = [];
            foreach($cart->products as $key) {
                $data['cartProducts'][] = $key->id;
            }
        } else {
            //Проверяем наличие переменных в сессии
            $data['sum'] = session('sum');
            if (is_null($data['sum'])) {
                $data['sum'] = 0;
                session(['sum' => $data['sum']]);
            }
            $data['count'] = session('count');
            if (is_null($data['count'])) {
                $data['count'] = 0;
                session(['count' => $data['count']]);
            }
            $data['cartProducts'] = session('cartProducts');
            if (is_null($data['cartProducts'])) {
                $data['cartProducts'] = [];
                session(['cartProducts' => $data['cartProducts']]);
            }
        }

        //Подгружаем информацию о товарах
        $products = Product::whereIn('id', $data['cartProducts'])->get();
        return view('cart', ['data' => $data, 'products' => $products]);
    }

    public function cartAjax(Request $request) {
        //Если пользователь авторизован, хранит его корзину в базе, если нет - в сессии
        if (Auth::check()) {
            $id = Auth::id();
            $cart = Cart::where('user_id', $id)->get();

            //Добавляем товар в корзину
            if ($request->action == 'add') {
                if (count($cart) == 0) {
                    //Создаем новую корзину для пользователя
                    $cart = new Cart();
                    $cart->user_id = $id;
                    $cart->sum = 0;
                    $cart->save();
                } else {
                    $cart = $cart[0];
                }
                //Проверяем на наличие товара в корзине
                $exist = $cart->products()->where('product_id', $request->id)->exists();
                if ($exist) {
                    return -1;
                } else {
                    $product = Product::find($request->id);
                    $cart->sum += $product->price;
                    $cart->save();

                    $cart->products()->attach($request->id);
                    return $cart->sum;
                }
            }

            //Удаляем товар из корзины
            if ($request->action == 'del' && count($cart) != 0) {
                $cart = $cart[0];
                $product = Product::find($request->id);
                //Проверяем на наличие товара в корзине
                $exist = $cart->products()->where('product_id', $request->id)->exists();
                if ($exist) {
                    $cart->sum -= $product->price;
                    $cart->save();

                    $cart->products()->detach($request->id);

                    $data['cartProducts'] = [];
                    foreach($cart->products as $key) {
                        $data['cartProducts'][] = $key->id;
                    }

                    //Подгружаем информацию о товарах
                    $products = Product::whereIn('id', $data['cartProducts'])->get();

                    return $cart->sum . '::' . $cart->products()->count() . '::' . view('ajax.cart', ['products' => $products])->render();
                }
            }
        } else {
            //Проверяем наличие переменных в сессии
            $data['sum'] = session('sum');
            if (is_null($data['sum'])) {
                $data['sum'] = 0;
                session(['sum' => $data['sum']]);
            }
            $data['count'] = session('count');
            if (is_null($data['count'])) {
                $data['count'] = 0;
                session(['count' => $data['count']]);
            }
            $data['cartProducts'] = session('cartProducts');
            if (is_null($data['cartProducts'])) {
                $data['cartProducts'] = [];
                session(['cartProducts' => $data['cartProducts']]);
            }

            //Добавляем товар в корзину
            if ($request->action == 'add') {
                $product = Product::find($request->id);
                //Проверяем на наличие товара в корзине
                if (in_array($product->id, $data['cartProducts'])) {
                    return -1;
                } else {
                    session(['sum' => $data['sum'] + $product->price]);
                    session(['count' => $data['count'] + 1]);
                    $data['cartProducts'][] = $request->id;
                    session(['cartProducts' => $data['cartProducts']]);
                    return $data['sum'] + $product->price;
                }
            }

            //Удаляем товар из корзины
            if ($request->action == 'del') {
                $product = Product::find($request->id);
                //Проверяем на наличие товара в корзине
                if (in_array($product->id, $data['cartProducts'])) {
                    $total = $data['sum'] - $product->price;
                    $count = $data['count'] - 1;
                    session(['sum' => $total]);
                    session(['count' => $count]);
                    unset($data['cartProducts'][array_search($request->id, $data['cartProducts'])]);
                    session(['cartProducts' => $data['cartProducts']]);

                    //Подгружаем информацию о товарах
                    $products = Product::whereIn('id', $data['cartProducts'])->get();

                    return $total . '::' . $count . '::' . view('ajax.cart', ['products' => $products])->render();
                }
            }
        }

    }
}

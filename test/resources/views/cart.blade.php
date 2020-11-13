@extends('layout')

@section('title')Корзина@endsection

@section('content')
    <div class="col-md-4">

        @if ($products->count() > 0)
        <h3>В корзине товаров <span class="count">{{ $data['count'] }}</span> на сумму <span class="cart">{{ $data['sum'] }}</span>руб</h3>
        <ul class="list-group">
            @foreach($products as $product)
                <li class="list-group-item d-flex align-items-center justify-content-between">{{ $product->name }}<span class="badge badge-primary badge-pill">{{ $product->price }}руб</span><button class="btn btn-danger" onclick="delCart(this)" data-id="{{ $product->id }}">Удалить</button></li>
            @endforeach
        </ul>
        @else
            <h3>Корзина пуста!</h3>
        @endif
    </div>
@endsection


@section('js')
    <script>

            function delCart(elem) {
                let id = $(elem).attr('data-id');

                $.ajax({
                    url: "{{ route('cartAjax') }}",
                    type: 'POST',
                    data: {
                        id: id,
                        action: 'del'
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (data) => {
                        //Выводим товары
                        let result = data.split('::');
                        if (result[0] == 0) {
                            $('.col-md-4').html('<h3>Корзина пуста!</h3>');
                        } else {
                            $('.cart').html(result[0]);
                            $('.count').html(result[1]);
                            $('.list-group').html(result[2]);
                        }
                    }
                });
            }
    </script>

@endsection


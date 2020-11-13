@extends('layout')

@section('title')Страница товаров@endsection

@section('content')
    <div class="col-md-4">
        <button class="btn btn-outline-primary col-md-5 sort" data-state="asc" data-field="price">Oтсортировать по цене (по возрастанию)</button>
        <button class="btn btn-outline-primary col-md-5 sort" data-state="asc" data-field="name">Oтсортировать по названию (А-Я)</button>
        <ul class="list-group">
        @foreach($products as $product)
            <li class="list-group-item d-flex align-items-center justify-content-between">{{ $product->name }}<span class="badge badge-primary badge-pill">{{ $product->price }}руб</span><button class="btn btn-success" onclick="addCart(this)" data-id="{{ $product->id }}">Купить</button></li>
        @endforeach
        </ul>
    </div>
@endsection


@section('js')
    <script>
        $(document).ready(function() {
            $('.sort').click(function() {
                let state = $(this).attr('data-state');
                let field = $(this).attr('data-field');
                let elem = this;

                $.ajax({
                    url: "{{ route('home') }}",
                    type: 'GET',
                    data: {
                        state: state,
                        field: field
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (data) => {
                        //Выводим товары
                        $('.list-group').html(data);
                        //Изменяем значение кнопок
                        if (state === 'asc') {
                            $(elem).attr('data-state', 'desc');
                        } else {
                            $(elem).attr('data-state', 'asc');
                        }
                        if (state === 'asc' && field === 'price') {
                            $(elem).html('Oтсортировать по цене (по убыванию)');
                        }
                        if (state === 'desc' && field === 'price') {
                            $(elem).html('Oтсортировать по цене (по возрастанию)');
                        }
                        if (state === 'asc' && field === 'name') {
                            $(elem).html('Oтсортировать по названию (Я-А)');
                        }
                        if (state === 'desc' && field === 'name') {
                            $(elem).html('Oтсортировать по названию (А-Я)');
                        }
                    }
                });
            });
        });

        function addCart(elem) {
            let id = $(elem).attr('data-id');
            console.log(id);
            console.log(elem);
            $.ajax({
                url: "{{ route('cartAjax') }}",
                type: 'POST',
                data: {
                    id: id,
                    action: 'add'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (data) => {
                    //Выводим товары
                    if (data == -1) {
                        $(elem).html('Товар уже в корзине!');
                    } else {
                        $(elem).html('В корзине!');
                        $('.cart').html(data);
                    }
                    $(elem).attr('disabled', 'disabled');
                }
            });
        }
    </script>

@endsection

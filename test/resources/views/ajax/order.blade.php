@foreach($products as $product)
    <li class="list-group-item d-flex align-items-center justify-content-between">{{ $product->name }}<span class="badge badge-primary badge-pill">{{ $product->price }}руб</span><button class="btn btn-success" onclick="addCart(this)" data-id="{{ $product->id }}">Купить</button></li>
@endforeach

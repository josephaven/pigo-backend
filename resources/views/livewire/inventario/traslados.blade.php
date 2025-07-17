@php($tabActivo = 'traslados')

@section('title', 'Inventario')

@section('tabs')
    @include('components.inventario-tabs', ['tabActivo' => $tabActivo])
@endsection
<div>
    {{-- The whole world belongs to you. --}}
</div>

@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')
{{-- test --}}
@section('main')
    <div class="col-sm-8">
      <div class="card">
        <div class="card-header">Cтена</div>
        <div class="card-body">
          @if (session('status'))
          <div class="alert alert-success" role="alert">
            {{ session('status') }}
          </div>          
          @endif
          Тест
        </div>
      </div>     
    </div>    
@endsection
    


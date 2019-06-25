@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')

@section('main')        
          <div class="col-sm-8"> 
              <form role="form" action="{{route('delete_order')}}" method=POST>
              {{ csrf_field() }}
              @foreach($Orders as $Order)
                <div class="card">
                <div class="card-header">
                  <a href=https://vk.com/id{{$Order->Usersvk->user_id}} target=_blank title="Просмотреть пользователя">
                  <img src={{$Order->Usersvk->photo}} alt="..." class="img-rounded" height="50px"></a>
                  <a href=https://vk.com/id{{$Order->Usersvk->user_id}} target=_blank title="Просмотреть пользователя id{{$Order->Usersvk->user_id}}" >{{$Order->Usersvk->firstname}} {{$Order->Usersvk->lastname}}</a>
                </div>
                <div class="card-body">
                  <div class="checkbox">
                    <label>                  
                    <input type="checkbox" name="order_id[{{$loop->index}}]" value="{{$Order->id}}">
                    Заказ №{{$Order->id}} :
                    Заказано {{$Order->ordered}} :
                    Выполнено {{$Order->executed}}                  
                    </label>
                  </div>
                  
                </div> 
                </div>
              @endforeach
              <hr>
              <div class="form-group">
              <button id="btn_delete_order" type="submit" class="btn btn-primary" name="btn_delete_order">Удалить</button>
              </div>
          </div>         
@endsection('main')

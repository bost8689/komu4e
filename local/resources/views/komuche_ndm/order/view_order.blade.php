@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')

@section('main')        
          <div class="col-sm-8"> 
            <div class="card">
                <div class="card-header">   
                Кол-во заказов               
                </div>
                <div class="card-body">
                  @foreach ($profit as $nameProfit => $valueProfit)
                  {{$nameProfit }} {{$valueProfit}} <br>
                  @endforeach                 
                  <hr>
                </div> 
              </div>
              <hr>
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
                    Заказ №{{$Order->id}} {{date("d-m-Y", strtotime($Order->created_at))}}
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

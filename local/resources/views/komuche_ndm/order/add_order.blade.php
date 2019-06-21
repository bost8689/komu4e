@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')

@section('main')
        
          <div class="col-sm-8"> 
            <div class="card">
            <div class="card-header"> Добавление заказа           
            </div>
            <div class="card-body">
            
              @if ($collectOrder['order_type']=='Пост от своего имени')
              <form class="form-horizontal" role="form" action="{{route('processing_add_order')}}" method="post"> 
              {{ csrf_field() }}
              <div class="form-group">
                <label for="order_type" class="col-sm-14 control-label">Тип заказа</label>
                <div class="col-sm-14">                
                  <input type="text" class="form-control" id="order_type" name="order_type" value="{{$collectOrder['order_type']}}" placeholder="Тип заказа" readonly>
                </div>
              </div>
              <div class="form-group">
                <label for="order_date" class="col-sm-14 control-label">Дата</label>
                <div class="col-sm-14">
                  <input type="date" name="order_date" value={{date('Y-m-d',time())}} class="form-control" id="order_date" placeholder="date">
                </div>
              </div>
              <div class="form-group">
                <label for="usersvk_id" class="col-sm-14 control-label">Ссылка</label>
                <div class="col-sm-14">                
                  <input type="text" class="form-control" id="usersvk_id" name="usersvk_id" placeholder="Ссылка на страницу пользователя">
                </div>
              </div>
              <div class="form-group">
                <label for="order_count" class="col-sm-14 control-label">Кол-во</label>
                <div class="col-sm-14">                
                  <input type="text" class="form-control" id="order_count" name="order_count" placeholder="Количество">
                </div>
              </div>
              <div class="form-group">
                <label for="order_comment" class="col-sm-14 control-label">Комментарий</label>
                <div class="col-sm-14">                
                  <input type="text" class="form-control" id="order_comment" name="order_comment" placeholder="Комментарий (не обязательное поле)">
                </div>
              </div>
              <div class="form-group">
              <div class="checkbox">
              <div class="col-sm-offset-0 col-sm-14">
                <label>
                  <input type="checkbox" name=order_send value="отправить сообщение заказчику"> Отправить сообщение заказчику
                </label>
              </div>
              </div>
            </div>
              <div class="form-group">
                <div class="col-sm-offset-0 col-sm-14">
                  <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
              </div>
            </form>
              @elseif ($collectOrder['order_type']=='Пост от имени группы')
              В разработке ... 
              @elseif ($collectOrder['order_type']=='Закреп')
              В разработке ... 
              @elseif ($collectOrder['order_type']=='Визитка')
              В разработке ... 
              @elseif ($collectOrder['order_type']=='')
              Не выбран тип заказа
              @endif
              </div>
            </div>
          </div>
         
@endsection('main')
@section("js")

@endsection('js')
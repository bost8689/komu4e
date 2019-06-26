@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')
@section('main')
    <div class="col-sm-8">
      @if (session('status'))
      <div class="alert alert-success" role="alert">
        {{ session('status') }}
      </div>          
      @endif
          <form role="form" action="{{route('processing_message')}}" method=POST>
          {{ csrf_field() }}
          <input type="hidden" name="group_type" value="{{$c_peers['group_type']}}">
          Кол-во сообщений {{$c_peers['countPeers']}}
          @if(!empty($c_peers['peers']))
            @foreach($c_peers['peers'] as $peer)
            <div class="card">
            <div class="card-header">
              <img src={{$peer['Usersvk']->photo}} alt="..." class="img-rounded" height="50px"></a>
              <a href=https://vk.com/id{{$peer['Usersvk']->user_id}} target=_blank title="Просмотреть пользователя id{{$peer['Usersvk']->user_id}}" >{{$peer['Usersvk']->firstname}} {{$peer['Usersvk']->lastname}}</a>
                 
              <input type="hidden" name="messages[{{$loop->index}}][usersvk_id]" value="{{$peer['Usersvk']->id}}">
              <input type="hidden" name="messages[{{$loop->index}}][user_id]" value="{{$peer['Usersvk']->user_id}}">
            </div>
            <div class="card-body">              
              @if(isset($peer['banUsersvk']))
              <div class="alert alert-danger" role="alert">
              Внимание: Пользователь заблокирован 
              </div>
              @endif                             
              <textarea class="form-control normal" rows = "0" name="messages[{{$loop->index}}][text_send]"></textarea> 
              <div class="form-group">
              <select class="form-control" name="messages[{{$loop->index}}][status]" size=1>
              <option value="" selected></option>
              <option value=Прайс>Прайс</option>
              <option value=Реквизиты>Реквизиты</option>
              <option value=ПринятьВГруппу>Принять в группу</option>
              <option value=Разблокировать>Разблокировать</option>
              <option value=ОшибкаГруппой>Ошибка группой</option>
              </select>
              </div> 
               

              @foreach ($peer['messages'] as $message)
                {{$loop->index+1}} {{$message['from_name']}}<br>
                {{$message['text']}}
                @if(isset($message['photo']))
                  @foreach ($message['photo'] as $photo)
                    <a href={{$photo['url_photo_type_max']}} target=_blank title="Просмотреть фото">
                    <img src={{$photo['url_photo_type_min']}} alt="..." class="img-rounded"></a>
                  @endforeach
                @endif
                <br>
              @endforeach
            <br>
            <br>
            </div>
            </div>
            @endforeach
          @endif
          <div class="form-group">
          <button id="btn_processing_message" type="submit" class="btn btn-primary" name="btn_processing_message">Обработать</button>
          {{--<div class="checkbox">
           <label>
            <input type="checkbox" name=checkbox_prosmotreno value=Просмотрено checked="checked">
            Пометить, как просмотренные
            </label>
          </div>--}}
          </div>
          </form>          
             
    </div>    
@endsection
    


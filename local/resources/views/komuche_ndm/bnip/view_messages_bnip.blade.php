@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')
{{-- test --}}
@section('main')
    <div class="col-sm-8">
      @if (session('status'))
      <div class="alert alert-success" role="alert">
        {{ session('status') }}
      </div>          
      @endif
          Кол-во сообщений {{$peers['countPeers']}}
          <form role="form" action="{{route('processing_message_bnip')}}" method=POST>
          {{ csrf_field() }}
          @if(!empty($peers['peers']))
            @foreach($peers['peers'] as $peer)
            <div class="card">
            <div class="card-header">
              <img src={{$peer['Usersvk']->photo}} alt="..." class="img-rounded" height="50px"></a>
              <a href=https://vk.com/id{{$peer['Usersvk']->user_id}} target=_blank title="Просмотреть пользователя id{{$peer['Usersvk']->user_id}}" >{{$peer['Usersvk']->firstname}} {{$peer['Usersvk']->lastname}}</a>
              @if(isset($peer['banUsersvk']))
                Пользователь заблокирован
              @endif

              <input type="hidden" name="bnip[{{$loop->index}}][usersvk_id]" value="{{$peer['Usersvk']->id}}">              
            </div>
            <div class="card-body">
              <textarea class="form-control normal" rows = "0" name="bnip[{{$loop->index}}][text_send]"></textarea> 
              <div class="form-group">
              <select class="form-control" name="bnip[{{$loop->index}}][status]" size=1>
              <option value="" selected></option>
              <option value=Найдено>Найдено</option>
              <option value=Потеряно>Потеряно</option>
              <option value=Ошибка>Ошибка</option>
              <option value=Повтор>Повтор</option>                  
              </select> 
              </div>
              @foreach ($peer['messages'] as $message)
                {{$loop->index+1}} {{$message['from_name']}}<br>
                <div class="checkbox">
                 <label>
                  {{--checked="checked"--}}
                  <input type="checkbox" name="bnip[{{$loop->parent->index}}][message][{{$loop->index}}][text]" value="{{$message['text']}}">
                  {{$message['text']}}
                  </label>
                </div>
                @if(isset($message['photo']))
                  @foreach ($message['photo'] as $photo)                      
                      <div class="checkbox">
                      <label>
                      <input type="checkbox" name="bnip[{{$loop->parent->parent->index}}][message][{{$loop->parent->index}}][photo][{{$loop->index}}]" value="{{$photo['url_photo_type_max']}}">
                      <a href={{$photo['url_photo_type_max']}} target=_blank title="Просмотреть фото">
                      <img src={{$photo['url_photo_type_min']}} alt="..." class="img-rounded"></a>
                      </label>
                    </div>
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
          <button id="button_processing" type="submit" class="btn btn-primary" name=button_processing>Обработать</button>
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
    


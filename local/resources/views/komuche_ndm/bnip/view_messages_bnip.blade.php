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
          @if(!empty($peers['peers']))
            @foreach($peers['peers'] as $peer)
            <div class="card">
            <div class="card-header">
              <img src={{$peer['Usersvk']->photo}} alt="..." class="img-rounded" height="50px"></a>
              <a href=https://vk.com/id{{$peer['Usersvk']->user_id}} target=_blank title="Просмотреть пользователя id{{$peer['Usersvk']->user_id}}" >{{$peer['Usersvk']->firstname}} {{$peer['Usersvk']->lastname}}</a>
              @if(isset($peer['banUsersvk']))
                Пользователь заблокирован
              @endif              
            </div>
            <div class="card-body">
              <form role="form" action="{{route('processingMessage')}}" method=POST>
              {{ csrf_field() }}
              <textarea class="form-control normal" rows = "0" name=>тест</textarea> 
              <div class="form-group">
              <select class="form-control" name= size=1>
              <option value="" selected></option>
              <option value=Прайс>Прайс</option>
              <option value=Прайс>Реквизиты</option>              
              </select> 
              <button id="btn_send_message" type="submit" class="btn btn-primary" name=btn_send_message>Отправить</button>
              </div> 
              </form>    

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
             
    </div>    
@endsection
    


@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')

@section('main')
    <div class="col-sm-8">
      @if (session('status'))
        <div class="alert alert-success" role="alert">
          {{ session('status') }}
        </div>          
      @endif
      <form role="form" action="{{route('processing_postmessage')}}" method=POST>
      {{ csrf_field() }}  
      @if (count($PostmessagesYesterday)>0)
      <div class="alert alert-danger" role="alert">
      @else
      <div class="alert alert-success" role="alert"> 
      @endif
      Количество необработанных записей за предыдущие дни {{count($PostmessagesYesterday)}}
      </div>
      <div class="alert alert-success" role="alert"> 
      Кол-во новых постов: {{$lastCountAddDBPostmessage}} 
      Кол-во новых пользователей: {{$lastCountAddDBPostmessage}}
      Кол-во новых фото: {{$lastCountAddDBPhotosPost}}
      </div>

      @foreach($collectPostmessages as $collectPostmessage)
      <div class="card">
        <div class="card-header">
          <a href=https://vk.com/id{{$collectPostmessage['Usersvk']->user_id}} target=_blank title="Просмотреть пользователя">
          <img src={{$collectPostmessage['Usersvk']->photo}} alt="..." class="img-rounded" height="50px"></a>
          <a href=https://vk.com/id{{$collectPostmessage['Usersvk']->user_id}} target=_blank title="Просмотреть пользователя id{{$collectPostmessage['Usersvk']->user_id}}" >{{$collectPostmessage['Usersvk']->firstname}} {{$collectPostmessage['Usersvk']->lastname}}</a>
          
          <input  type="hidden" name="processing[{{$loop->index}}][usersvk_id]" value="{{$collectPostmessage['Usersvk']->id}}"> 
          <input  type="hidden" name="processing[{{$loop->index}}][user_id]" value="{{$collectPostmessage['Usersvk']->user_id}}">

                  
        </div>
        <div class="card-body">
          @if ($collectPostmessage['Orders']->count())
          
            <div class="alert alert-danger" role="alert">
              Внимание рекламодатель!<p>                      
              @foreach ($collectPostmessage['Orders'] as $Order)
                Заказ №{{$Order->id }} : Заказано {{$Order->ordered}} : Выполнено {{$Order->executed}}<p> 
                <input  type="hidden" name="processing[{{$loop->parent->index}}][Orders][{{$loop->index}}][id]" value="{{$Order->id}}">
              @endforeach  
            </div>  
                           
          @endif 
          
          @foreach($collectPostmessage['Postmessages'] as $Postmessage)            
{{-- Красим слова --}}
            <a href=https://vk.com/wall-46590816_{{$Postmessage->source_id}} target=_blank title="Просмотреть пост">{{date("d.m.Y H:i:s", strtotime($Postmessage->date))}}</a>
            @if ($Postmessage->status == "Найдено" or $Postmessage->status == "Потеряно")
            <span class="badge badge-warning">
            @elseif ($Postmessage->status == "Удалить")
            <span class="badge badge-danger"> 
            @elseif ($Postmessage->status == "Заказ")
            <span class="badge badge-success"> 
            @else
            <span class="badge badge-light">             
            @endif
            {{$Postmessage->status}} -
            @if (isset($Postmessage->status)) {{$Postmessage->user->name}}@endif
            </span>


            <br>
            {{$Postmessage->text}}<br>
            @foreach($Postmessage->photospostmessage as $Photopostmessage)
              <a href={{$Photopostmessage->photomax_url}} target=_blank title="Просмотреть фото">
              <img src={{$Photopostmessage->photomin_url}} alt="..." class="img-rounded"></a>              
            @endforeach 
            <br><br>
            <select class="form-control" name=processing[{{$loop->parent->index}}][postmessage_id][{{$Postmessage->id}}] size=1>
            <option value="" selected></option>
            <option value=Удалить>Удалить</option>
            <option value=Реклама>Реклама</option>
            <option value=Повтор>Повтор</option>
            <option value=Ссылка>Ссылка</option>  
            <option value=Более3>Более3</option>    
            <option value=Найдено>Найдено</option> 
            <option value=Потеряно>Потеряно</option>
            @if ($collectPostmessage['Orders']->count())
            <option value="Заказ">Выполнить заказ</option>
            @endif            
            </select>
          @endforeach
                            
        </div>
      </div> 
      @endforeach
      <hr>
      <div class="form-group">
      <button id="button_processing" type="submit" class="btn btn-primary" name=button_processing>Обработать</button>
      <div class="checkbox">
       <label>
        <input type="checkbox" name=checkbox_prosmotreno value=Просмотрено checked="checked">
        Пометить, как просмотренные
        </label>
      </div>
      </div>
      </form> 

      



    </div>    
@endsection
    


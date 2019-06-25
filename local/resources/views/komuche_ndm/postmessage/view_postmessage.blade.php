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

      @foreach($collectPostmessages as $collectPostmessage)
      <div class="card">
        <div class="card-header">
          <a href=https://vk.com/id{{$collectPostmessage['Usersvk']->user_id}} target=_blank title="Просмотреть пользователя">
          <img src={{$collectPostmessage['Usersvk']->photo}} alt="..." class="img-rounded" height="50px"></a>
          <a href=https://vk.com/id{{$collectPostmessage['Usersvk']->user_id}} target=_blank title="Просмотреть пользователя id{{$collectPostmessage['Usersvk']->user_id}}" >{{$collectPostmessage['Usersvk']->firstname}} {{$collectPostmessage['Usersvk']->lastname}}</a>
          @if ($collectPostmessage['Orders']->count())
            Внимание рекламодатель!
          @endif 
          <input  type="hidden" name="processing[{{$loop->index}}][usersvk_id]" value="{{$collectPostmessage['Usersvk']->id}}"> 
          <input  type="hidden" name="processing[{{$loop->index}}][user_id]" value="{{$collectPostmessage['Usersvk']->user_id}}">

          @if ($collectPostmessage['Orders']->count())
            @foreach ($collectPostmessage['Orders'] as $Order)
              <input  type="hidden" name="processing[{{$loop->parent->index}}][Orders][{{$loop->index}}][id]" value="{{$Order->id}}">
            @endforeach
                        
          @endif         
        </div>
        <div class="card-body">
          @foreach($collectPostmessage['Postmessages'] as $Postmessage)
            <a href=https://vk.com/wall-46590816_{{$Postmessage->source_id}} target=_blank title="Просмотреть пост" >{{date("d.m.Y H:i:s", strtotime($Postmessage->date))}}</a> {{$Postmessage->status}} @if (isset($Postmessage->status)) {{$Postmessage->user->name}} @endif          
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
    


@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')

@section('main')
  <div class="col-sm-8">

    @if (session('status'))
      <div class="alert alert-success" role="alert">
        {{ session('status') }}
      </div>          
    @endif      

    @foreach ($Postmessages as $Postmessage)
    <div class="card">
      <div class="card-header">
        <a href=https://vk.com/id{{$Postmessage->usersvk->user_id}} target=_blank title="Просмотреть пользователя">
        <img src={{$Postmessage->usersvk->photo}} alt="..." class="img-rounded" height="50px"></a>
        <a href=https://vk.com/id{{$Postmessage->usersvk->user_id}} target=_blank title="Просмотреть пользователя id{{$Postmessage->usersvk->user_id}}" >{{$Postmessage->usersvk->firstname}} {{$Postmessage->usersvk->lastname}}</a>
      </div>
      <div class="card-body">        
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
        {{$Postmessage->text}}<br>
        @foreach($Postmessage->photospostmessage as $Photopostmessage)
          <a href={{$Photopostmessage->photomax_url}} target=_blank title="Просмотреть фото">
          <img src={{$Photopostmessage->photomin_url}} alt="..." class="img-rounded"></a>              
        @endforeach 
      </div>
    </div> 
    @endforeach

  </div>    
@endsection
    


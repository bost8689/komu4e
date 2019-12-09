@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')

@section('main')
    <div class="col-sm-8">
      @if (session('status'))
        <div class="alert alert-success" role="alert">
          {{ session('status') }}
        </div>          
      @endif
      <form role="form" action="{{route('processingBnip')}}" method=POST>
      {{ csrf_field() }}  
      @foreach($cBnips as $v_Bnips)
      <div class="card">
        <div class="card-header">
          <a href=https://vk.com/id{{$v_Bnips['Usersvk']->user_id}} target=_blank title="Просмотреть пользователя">
          <img src={{$v_Bnips['Usersvk']->photo}} alt="..." class="img-rounded" height="50px"></a>
          <a href=https://vk.com/id{{$v_Bnips['Usersvk']->user_id}} target=_blank title="Просмотреть пользователя id{{$v_Bnips['Usersvk']->user_id}}" >{{$v_Bnips['Usersvk']->firstname}} {{$v_Bnips['Usersvk']->lastname}}</a>
        </div>
        <div class="card-body">
          @foreach($v_Bnips['Bnips']['cBnipTypeStatusNull'] as $k_BnipTypeStatusNull => $BnipTypeStatusNull)
            {{$BnipTypeStatusNull->created_at}}<br>
            {{$BnipTypeStatusNull->text}}            
            <br>
            @foreach($BnipTypeStatusNull->Photosbnip as $PhotoBnip)
              <a href={{$PhotoBnip->pathmax}}{{$PhotoBnip->filenamemax}} target=_blank title="Просмотреть фото">
              <img src={{$PhotoBnip->pathmax}}{{$PhotoBnip->filenamemax}} class="img-fluid" alt="..." width="120"></a>              
            @endforeach 
            <input  type="hidden" name="processingBnip[{{$BnipTypeStatusNull->id}}][bnip_id]" value="{{$BnipTypeStatusNull->id}}">
            <select class="form-control" name="processingBnip[{{$BnipTypeStatusNull->id}}][status]" >
            @if($BnipTypeStatusNull->status=='Найдено')
              <option value=""></option>
              <option value=Найдено selected>Найдено</option>
              <option value=Потеряно>Потеряно</option>
              <option value=Удалить>Удалить</option>
              <option value=Повтор>Повтор</option>
              <option value=ОшибкаГруппой>Ошибка Группой</option>
            @elseif($BnipTypeStatusNull->status=='Потеряно') 
              <option value=""></option>
              <option value=Найдено>Найдено</option>
              <option value=Потеряно selected>Потеряно</option>
              <option value=Удалить>Удалить</option>
              <option value=Повтор>Повтор</option>
              <option value=ОшибкаГруппой>Ошибка Группой</option>
            @elseif($BnipTypeStatusNull->status=='Удалить') 
              <option value=""></option>
              <option value=Найдено>Найдено</option>
              <option value=Потеряно>Потеряно</option>
              <option value=Удалить >Удалить</option>
              <option value=Повтор>Повтор</option>
              <option value=ОшибкаГруппой>Ошибка Группой</option>
            @elseif($BnipTypeStatusNull->status=='') 
              <option value="" selected></option>
              <option value=Найдено>Найдено</option>
              <option value=Потеряно>Потеряно</option>
              <option value=Удалить >Удалить</option>
              <option value=Повтор>Повтор</option>
              <option value=ОшибкаГруппой>Ошибка Группой</option>
            @endif
            
            </select>    
            <select class="form-control" name="processingBnip[{{$BnipTypeStatusNull->id}}][typeStatus]" >
            <option value="" selected></option>
            <option value=Ключи>Ключи</option>
            <option value=Документы>Документы</option>
            <option value=Телефон>Телефон</option>
            <option value=Карта>Карта</option>  
            <option value=Ювелирные>Ювелирные</option>
            <option value=Животные>Животные</option> 
            <option value=Одежда>Одежда</option>
            <option value=Разное>Разное</option>
            </select>         
          @endforeach 
          Список постов пользователя, которые были опубликованы ...<br>
          @if(isset($v_Bnips['Bnips']['cBnipTypeStatusIs']))
            @foreach($v_Bnips['Bnips']['cBnipTypeStatusIs'] as $k_BnipTypeStatusIs => $BnipTypeStatusIs)
              {{$BnipTypeStatusIs->created_at}}<br>
              {{$BnipTypeStatusIs->status}}-{{$BnipTypeStatusIs->type_status}}
              {{$BnipTypeStatusIs->text}}
              <br>
              @foreach($BnipTypeStatusIs->Photosbnip as $PhotoBnip)
                <a href={{$PhotoBnip->pathmax}}{{$PhotoBnip->filenamemax}} target=_blank title="Просмотреть фото">
                <img src={{$PhotoBnip->pathmax}}{{$PhotoBnip->filenamemax}} class="img-fluid" alt="..." width="120"></a>              
              @endforeach
              <br>
            @endforeach
          @endif                                         
        </div>
      </div> 
      @endforeach      
    
      <div class="form-group">
      <button id="btnProcessing" type="submit" class="btn btn-primary" name=btnProcessing>Обработать</button>
      {{-- <div class="checkbox">
       <label>
        <input type="checkbox" name=checkbox_prosmotreno value=Просмотрено checked="checked">
        Пометить, как просмотренные
        </label>
      </div> --}}
      </div>
      </form> 

      



    </div>    
@endsection
    


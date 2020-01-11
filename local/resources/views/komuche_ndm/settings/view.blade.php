@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')
@section('main')
<div class="col-sm-8"> 
  <div class="card">
    <div class="card-header">Режим отладки       
    </div>
    <div class="card-body">      
      <form class="form-horizontal" role="form" action="{{route('processing_settings')}}" method="post"> 
        {{ csrf_field() }}
        
          @if ($SettingsModeDebug->value2 == "Включить")
          <div class="alert alert-success" role="alert"> 
            Режим отладки включен
          @else
          <div class="alert alert-danger" role="alert">
            Режим отладки выключен
          @endif
          </div>

          <div class="form-group">
            <div class="col-sm-14">
              <select class="form-control" name="mode_debug">
                <option value=""></option>
                <option value="Включить">Включено</option>
                <option value="Выключить">Выключено</option>
              </select>
            </div>
          </div>

          <div class="col-sm-14">
            <button type="submit" class="btn btn-primary">Сохранить</button>
          </div>


        </form>
      </div>
    </div>
  </div>
  @endsection('main')
  @section("js")

  @endsection('js')
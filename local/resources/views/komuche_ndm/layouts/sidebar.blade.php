@section('sidebar')
<div class="container">
  <div class="row"> <!-- no-gutters убрать отступы-->
    @yield('main')
    <div class="col-sm-4">
      <!-- контейнер-->
      <!-- контейнер-->
      <div class="col-sm-14">
        <div class="card">
          <div class="card-header">Заказы</div>
          <div class="card-body">          
            <form class="form-horizontal" role="form" method="POST" action="{{route('add_order')}}">
              {{csrf_field()}} 
              <div class="form-group">
                <div class="col-sm-14">
                  <select class="form-control" name="order_type">
                    <option value=""></option>
                    <option value="Пост от своего имени">Пост от своего имени</option>
                    <option value="Пост от имени группы">Пост от имени группы</option>
                    <option value="Закреп">Закреп</option>
                    <option value="Визитка">Визитку</option>       
                  </select>
                </div>
              </div>
              <div class="col-sm-14">
                <button type="submit" class="btn btn-primary">Добавить</button>
              </div>
            </form>
            <p>
            <form role="form" action="{{route('view_order')}}" method="post"> 
            {{csrf_field()}}
            <div class="col-sm-14">
              <button type="submit" class="btn btn-primary">Показать</button>
            </div> 
          </form>
          </div>
        </div>     
      </div>
      <!-- контейнер-->
      <!-- контейнер-->
      <div class="col-sm-14">
        <div class="card">
          <div class="card-header">Записи</div>
          <div class="card-body"> 
            <form class="form-horizontal" role="form" method="POST" action="{{ route('view_postmessage') }}">
              {{csrf_field()}} 
              <div class="form-group">              
                <div class="col-sm-14">
                 <input type=date class="form-control" name=date_view_wall value={{ date('Y-m-d',time()) }}>
               </div>
             </div>
             <div class="checkbox">
              <label>
              <input type="checkbox" name=checkbox_all value="Показать все записи">Показать все записи<br>                
              </label>
              </div> 
              <div class="checkbox">
              <label>  
              <input type="checkbox" name=checkbox_status_is value="Показать только обработанные">Показать только обработанные
              </label>             
            </div> 
            <div class="col-sm-14">
              <button type="submit" class="btn btn-primary" name="btn_view_wall">Найти</button>
            </div>          
          </form>
          <p>
          <form class="form-horizontal" role="form" method="POST" action="{{ route('updateEvent') }}">
            {{ csrf_field() }} 
            <div class="col-sm-14">
              <button type="submit" class="btn btn-primary" name="btn_update_event">Обновить</button>
            </div>          
          </form>
        </div>
      </div>     
    </div>
    <!-- контейнер-->
    <div class="col-sm-14">
      <div class="card">
        <div class="card-header">Сообщения</div>
        <div class="card-body">
          <form role="form" action="{{route('view_message')}}" method="post"> 
            {{csrf_field()}}
            <div class="form-group">
              <div class="col-sm-14">
                <select class="form-control" name="group_type">
                  <option value=""></option>
                  <option value="Объявления">Объявления</option>
                  <option value="Городская">Городская</option>                      
                </select>
              </div> 
            </div>           
              <div class="col-sm-14">
                <button type="submit" class="btn btn-primary">Сообщения</button>
              </div>
             
          </form>
        </div>
      </div>     
    </div>
    <!-- контейнер-->
    <!-- контейнер-->
    <div class="col-sm-14">
      <div class="card">
        <div class="card-header">Потеряшка</div>
        <div class="card-body">
          <form role="form" action="{{ route('view_bnip')}}" method="post"> 
            {{csrf_field()}}
            <div class="col-sm-14">
              <button type="submit" class="btn btn-primary">Показать</button>
            </div> 
          </form>
          <p>
          <form role="form" action="{{route('view_message_bnip')}}" method="post"> 
            {{csrf_field()}}
            <div class="col-sm-14">
              <button type="submit" class="btn btn-primary">Сообщения</button>
            </div> 
          </form>
        </div>
      </div>     
    </div>
    <!-- контейнер-->
    <div class="col-sm-14">
      <div class="card">
        <div class="card-header">Поиск</div>
        <div class="card-body">
          <form role="form" action="{{ route('find_postmessage')}}" method="post"> 
            {{csrf_field()}}
            <div class="form-group">
              <div class="col-sm-14">                            
                <input type="text" class="form-control" name="usersvk_id" placeholder="Ссылка пользователя">
              </div>
            </div>
            <div class="form-group">
              
              <input type="date" class="form-control" name="date_begin" value={{date('Y-m-d',time()-2592000)}}>
              
            </div>
            <div class="col-sm-14">
              <button type="submit" class="btn btn-primary">Найти</button>
            </div> 
          </form>
        </div>
      </div>     
    </div>
    <!-- контейнер--> 
  </div>
</div>
</div>
@endsection
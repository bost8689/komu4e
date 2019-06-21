@extends('layouts.app')

@section('content')
<!-- <div class="container">
        <div class="row">
        <div class="col-sm-4 col-sm-offset-0">
            1111111
        </div> 
        <div class="col-sm-4 col-sm-offset-0">
            222222222
        </div>      
</div>
</div> -->
<div class="container">
<div class="row">  
<div class="col-sm-9 col-md-9 col-sm-offset-0 blog-main">
<div class="panel panel-black">
<div class="panel-heading">Заказы: Тип заказа: Пост от своего имени <br></div>

<div class="panel-body">
	<p class="text-success">Количество открытых заказов: {{$orders->count()}} </p>
	@if (Auth::user()->email=='admin@komucheinfo.ru')	
	Посты от своего имени	<br>
	Выручка за предыдущий месяц {{ $profit -> where('created_at','>',date('Y-m-01 00:00:00',strtotime("-1 month")))->where('created_at','<',date('Y-m-01 00:00:00'))->sum('ordered') * 100 }} руб.<br>
	Выполненных за предыдущий месяц {{ $profit -> where('created_at','>',date('Y-m-01 00:00:00',strtotime("-1 month")))->where('created_at','<',date('Y-m-01 00:00:00'))->sum('executed')}} руб.<br>
	Выручка за текущий месяц {{ $profit -> where('created_at','>',date('Y-m-01 00:00:00'))->sum('ordered') * 100 }} руб.<br>
	Выполненных за текущий месяц {{ $profit -> where('created_at','>',date('Y-m-01 00:00:00'))->sum('executed')}}
	@endif

</div>

</div>
<!--         <ol class="breadcrumb">
          <li><a href="/welcome">Главная</a></li>  
          <li class="active">Заказы</li>
        </ol> -->
         

 <!--        <table class="table">
        	
  <thead class="thead-primary">
    <tr>
      <th>#</th>
      <th>Имя</th>
      <th>Заказано</th>
      <th>Выполнено</th>

    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">33</th>
      <td>Марк</td>
      <td>Отто</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Джейкоб</td>
      <td>Тортон</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">4</th>
      <td>Ларри</td>
      <td>the Bird</td>
      <td>@twitter</td>
    </tr>
  </tbody>
</table> -->

       <!-- <div class="row">  -->

  



		        

        
    	
        @php
        $x_orders=0;
        $x_order=0;
        $unique_usersvk_ids=$orders->unique('usersvk_id');
        @endphp
        <form class="form-horizontal" role="form" action="{{route('delete_order')}}" method="post"> 
        {{ csrf_field() }}
        @foreach ($unique_usersvk_ids->chunk(4) as $unique_usersvk_ids)
        	<!-- <div class="col-sm-6 col-sm-offset-0"> -->
        	@foreach ($unique_usersvk_ids as $unique_usersvk_id)
	        @php    
	            $order_usersvk_ids = $orders->where('usersvk_id',$unique_usersvk_id->usersvk_id);
	        @endphp
	        <!-- <div class="blog-post">        
	        <p class="blog-post-title lead"> -->
	        <div class="panel panel-primary"> 
	        <div class="panel-heading"> 
          <img src={{$unique_usersvk_id->usersvk->photo}} alt="..." class="img-rounded" height="50px">  
	        <a class="text-white" href=https://vk.com/id{{$unique_usersvk_id->usersvk->user_id}} target=_blank title="Открыть страницу в ВК">{{$unique_usersvk_id->usersvk->firstname}} {{$unique_usersvk_id->usersvk->lastname}}</a>
	        @php                
	        $x_order=0;
	        @endphp
	        </div>
	            <div class="panel-body">
<!-- 	              <table class="table table-bordered">
				  <thead>
				    <tr>
				      <th>#</th>
				      <th>Дата</th>
				      <th>Заказано</th>
				      <th>Выполнено</th>
				    </tr>
				  </thead>
				  <tbody> -->
	            
	            <!-- <hr> -->
	            @foreach ($order_usersvk_ids as $order_usersvk_id)
<!-- 	                <tr>
				      <th scope="row">{{$order_usersvk_id->id}}</th>
				      <td>{{date("d.m.Y H:i:s", strtotime($order_usersvk_id->created_at))}}</td>
				      <td>{{$order_usersvk_id->ordered}}</td>
				      <td>{{$order_usersvk_id->executed}}</td>
				    </tr> -->
	                №{{$order_usersvk_id->id}} - {{date("d.m.Y H:i:s", strtotime($order_usersvk_id->created_at))}} | {{$order_usersvk_id->user->name}} | Заказано: {{$order_usersvk_id->ordered}} Выполнено: {{$order_usersvk_id->executed}} Комментарий:{{$order_usersvk_id->comments}}<br>  
	                <button type="submit" class="btn btn-primary" name=delete_order value={{$order_usersvk_id->id}}>удалить</button>       
	                @php                
	                    $x_order++;
	                @endphp
	                @if ($x_order!=count($order_usersvk_ids))
	                <hr>
	                @endif
	            @endforeach  
<!-- 	            </tbody>
				</table>   -->    
	            </div>
	        </div> <!-- blog-post -->
	        @endforeach
	        
	        <!-- </div> --> <!-- col-sm-4 -->
        @endforeach
        <!-- </div> --> <!-- row -->
          



<!--           <nav>
            <ul class="pager">
              <li><a href="#">Older</a></li>
              <li class="disabled"><a href="#">Newer</a></li>
            </ul>
          </nav> -->
</div>
@endsection('content')
@section("js")
@endsection('js')

@extends('layouts.app')

@section('content')
<div class="container">
<div class="row"> 
          <div class="col-sm-9 col-md-9 blog-main">
          <div class="panel panel-black">
                    <div class="panel-heading">Поиск записей на стене</div>
                    <div class="panel-body">                                              
                    	<p class="text-danger">{{ $result_find or ''}}</p>
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                    </div>
           </div>         
            


<!--           <nav>
            <ul class="pager">
              <li><a href="#">Older</a></li>
              <li class="disabled"><a href="#">Newer</a></li>
            </ul>
          </nav> -->
          </div>
@endsection('content')

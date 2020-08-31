@extends('layouts.app')
@extends('komuche_ndm.layouts.sidebar')
{{-- test --}}
@section('main')
    <div class="col-sm-8">
        @if (count($fileError) > 0)
        <div class="alert alert-danger" role="alert">
        Есть ошибки в программе {{count($fileError)}}. Сообщите администратору.
        </div>
        @endif 
      <div class="card">        
        <div class="card-header">Cтена</div>
<<<<<<< HEAD
        <div class="card-body">
            {{-- 
        	<audio src="audio.mp3" type="audio/mpeg" controls autoplay loop > нет аудио</audio>
            --}}
=======
        <div class="card-body"> 
        	<!-- <audio src="audio.mp3" type="audio/mpeg" controls loop > нет аудио</audio>

          <audio src="gimnbaraban.mp3" type="audio/mpeg" controls loop > нет аудио</audio> -->

>>>>>>> 44b4b2a406ccaacf146eb82639f2bf523880996c
          @if (session('status'))
          <div class="alert alert-success" role="alert">
            {{ session('status') }}
          </div>          
          @endif
          Тест
        </div>
      </div>     
    </div>    
    <script>

    // 	setInterval(function() {
    // 		console.log("tets");  
  		// }, 1000) //каждую секунду
    	
    </script>
@endsection
    


@extends('layouts/rightsidebar')

@section('title')
Блог {{ $user->username }}
@stop

@section('content')
<h1>Блог {{ $user->username }}</h1>

<div class="well">Карма, статистика и прочие плюшки.</div>

@if($is_author)
	<div class="user_controls">
		<a href="{{ route('post.create') }}" class="btn btn-default">Написать пост</a>
	</div>
@endif

@if( ! count($posts))
	<p>У пользователя пока нет постов.</p>
@else
	@foreach ($posts as $post)
		@include('blog.partials.short_blog_post')
	@endforeach
@endif
@stop

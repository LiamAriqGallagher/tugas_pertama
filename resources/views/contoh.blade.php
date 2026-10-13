@extends('layout')
@section('title', 'contoh halaman blade')
@section('header', $title)
@section('content')
    <h1>{{ $title }}</h1>
     <ol>
    @foreach ($products as $value)
        <li>{{ $value }}</li>  
    @endforeach
    </ol>
    @endsection
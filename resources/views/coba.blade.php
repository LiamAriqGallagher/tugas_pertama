@extends('layout')
@section('title', 'Contoh Halaman blade')
@section('header', 'Halaman Contoh')
@section('content')
    <h1>{{ $title }}</h1>
    @if($iflogin)
        <p>Selamat datang, pengguna!</p>
        @else
        <p>Silakan login untuk melanjutkan.</p>
    @endif
    <ol>
    @foreach($products as $product)
        <li>{{ $product }}</li>
    @endforeach
    </ol>
    <x-alert>
        <p>ini teks alert</p>
    </x-alert>
@endsection
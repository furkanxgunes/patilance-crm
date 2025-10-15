@extends('adminlte::page')

@section('content_top_nav_right')
    <x-notification-dropdown :user="auth()->user()" />
@stop

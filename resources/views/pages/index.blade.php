@extends('layouts.app')

@section('title', 'Eldarda Test Matrix')

@section('content')
    @include('chunks.form')
    @include('chunks.modal-response')
@endsection


@section('scripts')
    <script src="{{asset('js/form.js')}}"></script>
@endsection

@extends('pdfs.layouts.base')
@section('document-title', 'PROBE')
@section('content')
@php $L = 'Lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ut enim ad minim veniam quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat duis aute irure dolor in reprehenderit.'; @endphp

<div style="clear:both;"></div>
<div style="border:1px solid blue;">O. plain text right after clear:both at very top: {{ $L }}</div>

<br style="clear:both;">
<div style="border:1px solid green;">P. after br clear:both: {{ $L }}</div>

<div style="clear:left;"></div>
<div style="border:1px solid red;">Q. after clear:left: {{ $L }}</div>
@endsection

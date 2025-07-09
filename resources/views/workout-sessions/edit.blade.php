<?php /** @var \App\Models\WorkoutSession $workoutSession */ ?>
@extends('layouts.page')

@section('title', 'Workout Session')

@section('header')
    <h1>Workout: {{ $workoutSession->routine->name }}</h1>
@endsection

@section('content')

@endsection

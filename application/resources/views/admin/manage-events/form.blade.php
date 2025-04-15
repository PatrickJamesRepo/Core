@extends('layouts.app')

{{-- Set the page title dynamically --}}
@section('title', $event->exists ? $event->name : 'Create New Event')

@section('og')
    <meta property="og:title" content="{{ $event->exists ? $event->name : 'New Event' }}"/>
    <meta property="og:description" content="{{ $event->exists ? $event->description() : '' }}"/>
    <meta property="og:type" content="website"/>
    @if($event->exists && $event->image)
        <meta property="og:image" content="{{ asset($event->image) }}"/>
    @endif
    <meta name="twitter:card" content="summary_large_image"/>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    {{-- For the header image, if editing an event use its image; otherwise show a default placeholder --}}
                    <div class="card-img-top text-center p-3">
                        @if($event->exists && $event->image)
                            <img src="{{ asset('storage/' . $event->image) }}"
                                 alt="{{ $event->name }}"
                                 class="img-fluid">
                        @else
                            <p>No image available</p>
                        @endif
                    </div>

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fa fa-ticket"></i>
                            {{-- Display the appropriate title based on whether we’re creating a new event or editing --}}
                            {{ $event->exists ? __('Edit Event') : __('Create New Event') }}
                        </div>
                        <div>
                            <a href="{{ route('manage-events.index') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-list"></i>
                                {{ __('List Events') }}
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Display a main heading for the form --}}
                        <h1>{{ $event->exists ? $event->name : __('Create New Event') }}</h1>

                        {{-- The rest of your form fields go here; they can continue referencing $event safely. --}}
                        <form action="{{ $event->exists ? route('manage-events.update', $event) : route('manage-events.store') }}"
                              method="post" enctype="multipart/form-data">
                            @csrf
                            @if($event->exists)
                                @method('PUT')
                            @endif

                            {{-- Example for event name --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('Event Name') }}</label>
                                <input id="name" name="name"
                                       value="{{ old('name', $event->name) }}"
                                       type="text" class="form-control" required/>
                            </div>

                            {{-- More fields go here ... --}}

                            <button type="submit" class="btn btn-primary">
                                {{ $event->exists ? __('Update Event') : __('Create Event') }}
                            </button>
                        </form>
                    </div><!-- card-body -->
                </div><!-- card -->
            </div>
        </div>
    </div>
@endsection

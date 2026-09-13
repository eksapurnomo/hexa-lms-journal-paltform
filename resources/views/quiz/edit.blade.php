@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Quiz Edit'))

@push('styles')
    <style>
        .notValid {
            border: 2px solid red !important;
        }
    </style>
@endpush

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('course.index') }}">{{ __('Course') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quiz.index', $quiz->id) }}">{{ __('Quiz') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Edit') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="m-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="main-card card d-flex h-100 flex-column">
                        <div class="card-body">
                            <h5 class="card-title py-2">{{ __('Edit Quiz') }}</h5>
                            <form action="{{ route('quiz.update', $quiz->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label class="form-label" for="categoryInput">{{ __('Course') }}</label>
                                            <select id="categoryInput" class="form-select from-control" name="course_id"
                                                aria-hidden="true">
                                                @foreach ($courses as $course)
                                                    <option value="{{ $course->id }}"
                                                        {{ $course->id === $quiz->course?->id ? 'selected="selected"' : '' }}>
                                                        {{ $course->title }}</option>
                                                @endforeach
                                            </select>
                                            <p id="categoryError" class="text-danger my-1 "></p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="titleInput" class="form-label">{{ __('Quiz Title') }}</label>
                                            <input type="text" name="title" value="{{ $quiz->title }}" required
                                                class="form-control" id="titleInput"
                                                placeholder="{{ __('Enter quiz title') }}">
                                            <p id="titleInputError" class="text-danger my-1 "></p>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="durationInput" class="form-label">{{ __('Duration Per Question') }}
                                                <small>
                                                    ({{ __('In Second') }})</small></label>
                                            <input type="number" min="1" name="duration_per_question"
                                                value="{{ $quiz->duration_per_question }}" required class="form-control"
                                                id="durationInput" placeholder="{{ __('Enter duration per question') }}">
                                            <p id="duration_per_questionError" class="text-danger my-1 "></p>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="perQuestionMarkInput" class="form-label">
                                                {{ __('Marks Per Question') }}
                                            </label>
                                            <input type="number" min="1" name="mark_per_question"
                                                value="{{ $quiz->mark_per_question }}" required class="form-control"
                                                id="perQuestionMarkInput"
                                                placeholder="{{ __('Enter marks per question') }}">
                                            <p id="mark_per_questionError" class="text-danger my-1 "></p>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex">
                                            <label class="form-label fs-5 me-auto my-auto">{{ __('Questions') }}</label>
                                            <button type="button" class="btn btn-outline-primary my-3 me-2"
                                                onclick="addMcqQuestionItem()">+
                                                {{ __('Add Multiple Choice Question') }}</button>
                                            <button type="button" class="btn btn-primary my-3"
                                                onclick="addBinaryChoiceQuestionItem()">+
                                                {{ __('Add True/False Question') }}</button>
                                        </div>
                                        <div id="questionsWrapper">
                                            @foreach ($quiz->questions as $question)
                                                @if ($question->question_type == 'multiple_choice')
                                                    <div id="question{{ $loop->index }}"
                                                        class="row border border-primary p-3 mb-3">
                                                        <input type="hidden"
                                                            name="questions[{{ $loop->index }}][question_type]"
                                                            value="multiple_choice">
                                                        <div class="col-6">
                                                            <div class="mb-3">
                                                                <label for="textInput" class="form-label">
                                                                    {{ __('Question Text') }}
                                                                </label>
                                                                <input type="text" required
                                                                    name="questions[{{ $loop->index }}][question_text]"
                                                                    value="{{ $question->question_text }}"
                                                                    class="form-control" id="textInput"
                                                                    placeholder="{{ __('Enter question text') }}">
                                                            </div>
                                                        </div>

                                                        <div class="col-2">
                                                            <label class="form-label">&nbsp;</label>
                                                            <div>
                                                                <button type="button" class="btn btn-danger mb-4"
                                                                    onclick="removeQuestionItem({{ $loop->index }})">-
                                                                    {{ __('Remove Question') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $options = json_decode($question->options);
                                                        @endphp
                                                        <div class="col-12 border rounded-4"
                                                            id="question{{ $loop->index }}_options">
                                                            <div class="row py-3 mx-3">
                                                                <div class="col-6 d-flex mb-3">
                                                                    <label class="form-check-label my-auto me-3">
                                                                        {{ __('Option A') }}
                                                                    </label>
                                                                    <input class="form-control w-50 my-auto me-2"
                                                                        type="text"
                                                                        name="questions[{{ $loop->index }}][option_1][text]"
                                                                        value="{{ $options->option_1->text }}"
                                                                        placeholder="{{ __('Enter option text') }}">
                                                                    <input class="form-check-input my-auto me-2"
                                                                        type="checkbox" value="1"
                                                                        name="questions[{{ $loop->index }}][option_1][is_correct]"
                                                                        @if ($options->option_1->is_correct) checked @endif>
                                                                    <label class="form-check-label my-auto">
                                                                        {{ __('Correct') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col-6 d-flex mb-3">
                                                                    <label class="form-check-label my-auto me-3">
                                                                        {{ __('Option B') }}
                                                                    </label>
                                                                    <input class="form-control w-50 my-auto me-2"
                                                                        type="text"
                                                                        name="questions[{{ $loop->index }}][option_2][text]"
                                                                        value="{{ $options->option_2->text }}"
                                                                        placeholder="{{ __('Enter option text') }}">
                                                                    <input class="form-check-input my-auto me-2"
                                                                        type="checkbox" value="1"
                                                                        name="questions[{{ $loop->index }}][option_2][is_correct]"
                                                                        @if ($options->option_2->is_correct) checked @endif>
                                                                    <label class="form-check-label my-auto">
                                                                        {{ __('Correct') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col-6 d-flex">
                                                                    <label class="form-check-label my-auto me-3">
                                                                        {{ __('Option C') }}
                                                                    </label>
                                                                    <input class="form-control w-50 my-auto me-2"
                                                                        type="text"
                                                                        name="questions[{{ $loop->index }}][option_3][text]"
                                                                        value="{{ $options->option_3->text }}"
                                                                        placeholder="{{ __('Enter option text') }}">
                                                                    <input class="form-check-input my-auto me-2"
                                                                        type="checkbox" value="1"
                                                                        name="questions[{{ $loop->index }}][option_3][is_correct]"
                                                                        @if ($options->option_3->is_correct) checked @endif>
                                                                    <label class="form-check-label my-auto">
                                                                        {{ __('Correct') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col-6 d-flex">
                                                                    <label class="form-check-label my-auto me-3">
                                                                        {{ __('Option D') }}
                                                                    </label>
                                                                    <input class="form-control w-50 my-auto me-2"
                                                                        type="text"
                                                                        name="questions[{{ $loop->index }}][option_4][text]"
                                                                        value="{{ $options->option_4->text }}"
                                                                        placeholder="{{ __('Enter option text') }}">
                                                                    <input class="form-check-input my-auto me-2"
                                                                        type="checkbox" value="1"
                                                                        name="questions[{{ $loop->index }}][option_4][is_correct]"
                                                                        @if ($options->option_4->is_correct) checked @endif>
                                                                    <label class="form-check-label my-auto">
                                                                        {{ __('Correct') }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($question->question_type == 'single_choice')
                                                    <div id="question{{ $loop->index }}"
                                                        class="row border border-primary p-3 mb-3">
                                                        <input type="hidden"
                                                            name="questions[{{ $loop->index }}][question_type]"
                                                            value="multiple_choice">
                                                        <div class="col-6">
                                                            <div class="mb-3">
                                                                <label for="textInput" class="form-label">
                                                                    {{ __('Question Text') }}
                                                                </label>
                                                                <input type="text" required
                                                                    name="questions[{{ $loop->index }}][question_text]"
                                                                    value="{{ $question->question_text }}"
                                                                    class="form-control" id="textInput"
                                                                    placeholder="{{ __('Enter question text') }}">
                                                            </div>
                                                        </div>

                                                        <div class="col-2">
                                                            <label class="form-label">&nbsp;</label>
                                                            <div>
                                                                <button type="button" class="btn btn-danger mb-4"
                                                                    onclick="removeQuestionItem({{ $loop->index }})">-
                                                                    {{ __('Remove Question') }}</button>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $options = json_decode($question->options);
                                                        @endphp
                                                        <div class="col-12 border rounded-4"
                                                            id="question{{ $loop->index }}_options">
                                                            <div class="row py-3 mx-3">
                                                                <div class="col-6 d-flex mb-3">
                                                                    <label class="form-check-label my-auto me-3">
                                                                        {{ __('Option A') }}
                                                                    </label>
                                                                    <input class="form-control w-50 my-auto me-2"
                                                                        type="text"
                                                                        name="questions[{{ $loop->index }}][option_1][text]"
                                                                        value="{{ $options->option_1->text }}"
                                                                        placeholder="{{ __('Enter option text') }}">
                                                                    <input class="form-check-input my-auto me-2"
                                                                        type="checkbox" value="1"
                                                                        name="questions[{{ $loop->index }}][option_1][is_correct]"
                                                                        @if ($options->option_1->is_correct) checked @endif>
                                                                    <label class="form-check-label my-auto">
                                                                        {{ __('Correct') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col-6 d-flex mb-3">
                                                                    <label class="form-check-label my-auto me-3">
                                                                        {{ __('Option B') }}
                                                                    </label>
                                                                    <input class="form-control w-50 my-auto me-2"
                                                                        type="text"
                                                                        name="questions[{{ $loop->index }}][option_2][text]"
                                                                        value="{{ $options->option_2->text }}"
                                                                        placeholder="{{ __('Enter option text') }}">
                                                                    <input class="form-check-input my-auto me-2"
                                                                        type="checkbox" value="1"
                                                                        name="questions[{{ $loop->index }}][option_2][is_correct]"
                                                                        @if ($options->option_2->is_correct) checked @endif>
                                                                    <label class="form-check-label my-auto">
                                                                        {{ __('Correct') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col-6 d-flex">
                                                                    <label class="form-check-label my-auto me-3">
                                                                        {{ __('Option C') }}
                                                                    </label>
                                                                    <input class="form-control w-50 my-auto me-2"
                                                                        type="text"
                                                                        name="questions[{{ $loop->index }}][option_3][text]"
                                                                        value="{{ $options->option_3->text }}"
                                                                        placeholder="{{ __('Enter option text') }}">
                                                                    <input class="form-check-input my-auto me-2"
                                                                        type="checkbox" value="1"
                                                                        name="questions[{{ $loop->index }}][option_3][is_correct]"
                                                                        @if ($options->option_3->is_correct) checked @endif>
                                                                    <label class="form-check-label my-auto">
                                                                        {{ __('Correct') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col-6 d-flex">
                                                                    <label class="form-check-label my-auto me-3">
                                                                        {{ __('Option D') }}
                                                                    </label>
                                                                    <input class="form-control w-50 my-auto me-2"
                                                                        type="text"
                                                                        name="questions[{{ $loop->index }}][option_4][text]"
                                                                        value="{{ $options->option_4->text }}"
                                                                        placeholder="{{ __('Enter option text') }}">
                                                                    <input class="form-check-input my-auto me-2"
                                                                        type="checkbox" value="1"
                                                                        name="questions[{{ $loop->index }}][option_4][is_correct]"
                                                                        @if ($options->option_4->is_correct) checked @endif>
                                                                    <label class="form-check-label my-auto">
                                                                        {{ __('Correct') }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($question->question_type == 'single_choice')
                                                    <div id="question{{ $loop->index }}" class="row border p-3 mb-3">
                                                        <input type="hidden"
                                                            name="questions[{{ $loop->index }}][question_type]"
                                                            value="single_choice">
                                                        <div class="col-6">
                                                            <div class="mb-3">
                                                                <label for="textInput" class="form-label">
                                                                    {{ __('Question Text') }}
                                                                </label>
                                                                <input type="text" required
                                                                    name="questions[{{ $loop->index }}][question_text]"
                                                                    value="{{ $question->question_text }}"
                                                                    class="form-control" id="textInput"
                                                                    placeholder="{{ __('Enter question text') }}">
                                                            </div>
                                                        </div>

                                                        <div class="col-2">
                                                            <label class="form-label">&nbsp;</label>
                                                            <div>
                                                                <button type="button" class="btn btn-danger mb-4"
                                                                    onclick="removeQuestionItem({{ $loop->index }})">-
                                                                    {{ __('Remove Question') }}</button>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $options = json_decode($question->options);
                                                        @endphp
                                                        <div class="row border py-3 mx-3">
                                                            <div class="col-6 d-flex mb-3">
                                                                <label class="form-check-label my-auto me-3">
                                                                    {{ __('Option A') }}
                                                                </label>
                                                                <input class="form-control w-50 my-auto me-2"
                                                                    type="text"
                                                                    name="questions[{{ $loop->index }}][option_1][text]"
                                                                    value="{{ $options->option_1->text }}"
                                                                    placeholder="{{ __('Enter option text') }}">
                                                                <input class="form-check-input my-auto me-2"
                                                                    type="radio" value="option_1"
                                                                    name="questions[{{ $loop->index }}][correct_option]"
                                                                    @if ($options->option_1->is_correct) checked @endif>
                                                                <label class="form-check-label my-auto">
                                                                    {{ __('Correct') }}
                                                                </label>
                                                            </div>
                                                            <div class="col-6 d-flex mb-3">
                                                                <label class="form-check-label my-auto me-3">
                                                                    {{ __('Option B') }}
                                                                </label>
                                                                <input class="form-control w-50 my-auto me-2"
                                                                    type="text"
                                                                    name="questions[{{ $loop->index }}][option_2][text]"
                                                                    value="{{ $options->option_2->text }}"
                                                                    placeholder="{{ __('Enter option text') }}">
                                                                <input class="form-check-input my-auto me-2"
                                                                    type="radio" value="option_2"
                                                                    name="questions[{{ $loop->index }}][correct_option]"
                                                                    @if ($options->option_2->is_correct) checked @endif>
                                                                <label class="form-check-label my-auto">
                                                                    {{ __('Correct') }}
                                                                </label>
                                                            </div>
                                                            <div class="col-6 d-flex">
                                                                <label class="form-check-label my-auto me-3">
                                                                    {{ __('Option C') }}
                                                                </label>
                                                                <input class="form-control w-50 my-auto me-2"
                                                                    type="text"
                                                                    name="questions[{{ $loop->index }}][option_3][text]"
                                                                    value="{{ $options->option_3->text }}"
                                                                    placeholder="{{ __('Enter option text') }}">
                                                                <input class="form-check-input my-auto me-2"
                                                                    type="radio" value="option_3"
                                                                    name="questions[{{ $loop->index }}][correct_option]"
                                                                    @if ($options->option_3->is_correct) checked @endif>
                                                                <label class="form-check-label my-auto">
                                                                    {{ __('Correct') }}
                                                                </label>
                                                            </div>
                                                            <div class="col-6 d-flex">
                                                                <label class="form-check-label my-auto me-3">
                                                                    {{ __('Option D') }}
                                                                </label>
                                                                <input class="form-control w-50 my-auto me-2"
                                                                    type="text"
                                                                    name="questions[{{ $loop->index }}][option_4][text]"
                                                                    value="{{ $options->option_4->text }}"
                                                                    placeholder="{{ __('Enter option text') }}">
                                                                <input class="form-check-input my-auto me-2"
                                                                    type="radio" value="option_4"
                                                                    name="questions[{{ $loop->index }}][correct_option]"
                                                                    @if ($options->option_4->is_correct) checked @endif>
                                                                <label class="form-check-label my-auto">
                                                                    {{ __('Correct') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($question->question_type == 'binary')
                                                    <div id="question{{ $loop->index }}" class="row border p-3 mb-3">
                                                        <input type="hidden"
                                                            name="questions[{{ $loop->index }}][question_type]"
                                                            value="binary">
                                                        <div class="col-6">
                                                            <div class="mb-3">
                                                                <label for="textInput" class="form-label">
                                                                    {{ __('Question Text') }}
                                                                </label>
                                                                <input type="text" required
                                                                    name="questions[{{ $loop->index }}][question_text]"
                                                                    value="{{ $question->question_text }}"
                                                                    class="form-control" id="textInput"
                                                                    placeholder="{{ __('Enter question text') }}">
                                                            </div>
                                                        </div>

                                                        <div class="col-2">
                                                            <label class="form-label">&nbsp;</label>
                                                            <div>
                                                                <button type="button" class="btn btn-danger mb-4"
                                                                    onclick="removeQuestionItem({{ $loop->index }})">-
                                                                    {{ __('Remove Question') }}</button>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $options = json_decode($question->options);
                                                        @endphp
                                                        <div class="row border py-3 mx-3">
                                                            <div class="d-flex mb-3">
                                                                <input class="form-check-input my-auto me-2"
                                                                    type="radio" value="yes"
                                                                    name="questions[{{ $loop->index }}][correct_option]"
                                                                    @if ($options->yes->is_correct) checked @endif>
                                                                <label class="form-check-label my-auto">
                                                                    {{ __('True') }}
                                                                </label>
                                                            </div>
                                                            <div class="d-flex">
                                                                <input class="form-check-input my-auto me-2"
                                                                    type="radio" value="no"
                                                                    name="questions[{{ $loop->index }}][correct_option]"
                                                                    @if ($options->no->is_correct) checked @endif>
                                                                <label class="form-check-label my-auto">
                                                                    {{ __('False') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="col-12">
                                <button type="button" id="submitButton"
                                    class="btn btn-primary btn-lg px-5 py-2">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>

@endsection
@push('scripts')
    <script>
        var questionCounter = $('#questionsWrapper').children().length;


        function addMcqQuestionItem() {
            var questionRow = `<div id="question${questionCounter}" class="row border border-primary rounded-3 p-3 mb-3">
                            <input type="hidden" name="questions[${questionCounter}][question_type]" value="multiple_choice">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="textInput" class="form-label">{{ __('Question Title') }} ({{ __('can add multiple choice') }}.)</label>
                                        <input type="text" name="questions[${questionCounter}][question_text]"
                                            class="form-control" id="textInput"
                                            placeholder="{{ __('Enter question text') }}">
                                    </div>
                                </div>

                                <div class="col-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="button" class="btn btn-danger mb-4"
                                            onclick="removeQuestionItem(${questionCounter})">-
                                            {{ __('Remove Question') }}</button>
                                    </div>
                                </div>
                                <div class="col-12 border rounded-4" id="question${questionCounter}_options">
                                    <div class="row py-3 mx-3">
                                        <div class="col-6 d-flex mb-3">
                                            <label class="form-check-label my-auto me-3">
                                                {{ __('Option A') }}
                                            </label>
                                            <input class="form-control w-50 my-auto me-2" type="text"
                                                name="questions[${questionCounter}][option_1][text]" placeholder="{{ __('Enter option text') }}">
                                            <input class="form-check-input my-auto me-2" type="checkbox"
                                                value="1" name="questions[${questionCounter}][option_1][is_correct]">
                                            <label class="form-check-label my-auto">
                                                {{ __('Correct') }}
                                            </label>
                                        </div>
                                        <div class="col-6 d-flex mb-3">
                                            <label class="form-check-label my-auto me-3">
                                                {{ __('Option B') }}
                                            </label>
                                            <input class="form-control w-50 my-auto me-2" type="text"
                                                name="questions[${questionCounter}][option_2][text]" placeholder="{{ __('Enter option text') }}">
                                            <input class="form-check-input my-auto me-2" type="checkbox"
                                                value="1" name="questions[${questionCounter}][option_2][is_correct]">
                                            <label class="form-check-label my-auto">
                                                {{ __('Correct') }}
                                            </label>
                                        </div>
                                        <div class="col-6 d-flex">
                                            <label class="form-check-label my-auto me-3">
                                                {{ __('Option C') }}
                                            </label>
                                            <input class="form-control w-50 my-auto me-2" type="text"
                                                name="questions[${questionCounter}][option_3][text]" placeholder="{{ __('Enter option text') }}">
                                            <input class="form-check-input my-auto me-2" type="checkbox"
                                                value="1" name="questions[${questionCounter}][option_3][is_correct]">
                                            <label class="form-check-label my-auto">
                                                {{ __('Correct') }}
                                            </label>
                                        </div>
                                        <div class="col-6 d-flex">
                                            <label class="form-check-label my-auto me-3">
                                                {{ __('Option D') }}
                                            </label>
                                            <input class="form-control w-50 my-auto me-2" type="text"
                                                name="questions[${questionCounter}][option_4][text]" placeholder="{{ __('Enter option text') }}">
                                            <input class="form-check-input my-auto me-2" type="checkbox"
                                                value="1" name="questions[${questionCounter}][option_4][is_correct]">
                                            <label class="form-check-label my-auto">
                                                {{ __('Correct') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>`

            $('#questionsWrapper').append(questionRow);
            ++questionCounter;
        }


        function addBinaryChoiceQuestionItem() {
            var questionRow = `<div id="question${questionCounter}" class="row border border-primary rounded-3 p-3 mb-3">
                        <input type="hidden" name="questions[${questionCounter}][question_type]" value="binary">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="textInput" class="form-label">Question Title</label>
                                <input type="text" required name="questions[${questionCounter}][question_text]"
                                       class="form-control" id="textInput" placeholder="{{ __('Enter question text') }}">
                            </div>
                        </div>

                        <div class="col-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-danger mb-4"
                                        onclick="removeQuestionItem(${questionCounter})">-
                                    {{ __('Remove Question') }}
                                </button>
                            </div>
                        </div>
                        <div class="col-12 border rounded-4" id="question${questionCounter}_options">
                            <div class="row py-3 mx-3">
                            <div class="d-flex mb-3">
                                <input class="form-check-input my-auto me-2" type="radio"
                                       value="yes" name="questions[${questionCounter}][correct_option]" required>
                                <label class="form-check-label my-auto">
                                    {{ __('True') }}
                                </label>
                            </div>
                            <div class="d-flex">
                                <input class="form-check-input my-auto me-2" type="radio"
                                       value="no" name="questions[${questionCounter}][correct_option]" required>
                                <label class="form-check-label my-auto">
                                    {{ __('False') }}
                                </label>
                            </div>
                        </div>
                        </div>
                    </div>`;

            $('#questionsWrapper').append(questionRow);
            ++questionCounter;
        }

        function removeQuestionItem(elementNumber) {
            $(`#question${elementNumber}`).remove();
        }


        // function removeQuestionItem(elementNumber) {
        //     $(`#question${elementNumber}`).remove();
        // }

        $('#submitButton').on('click', function(e) {
            e.preventDefault();

            const courseStatus = validateCourseContents();
            const questionStatus = validateQuestionsWrapper()

            console.log(`courseStatus: ${courseStatus}`, `questionStatus: ${questionStatus}`);

            if (questionStatus && courseStatus) {
                $('form').submit()
            } else {
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                Toast.fire({
                    icon: "error",
                    title: "Kindly review the form,there found some missing information and try again."
                });
            }
        })

        function validateCourseContents() {

            let [categoryValue, titleInput, durationInput, perQuestionMarkInput] = [$('#categoryInput')
                .val(), $('#titleInput').val(), $('#durationInput').val(), $('#perQuestionMarkInput').val()
            ]
            let [categoryError, titleError, durationError, perQuestionMarkError] = [$('categoryError'), $(
                '#titleInputError'), $('#duration_per_questionError'), $('#mark_per_questionError')];

            // rest errors
            categoryError.text('')
            titleError.text('')
            durationError.text('')
            perQuestionMarkError.text('')

            if (!categoryValue) {
                categoryError.text('Oops! You missed this. Please complete the field.');
            }

            if (!titleInput) {
                titleError.text('Oops! You missed this. Please complete the field.');
            }

            if (!durationInput) {
                durationError.text('Oops! You missed this. Please complete the field.');
            }

            if (!perQuestionMarkInput) {
                perQuestionMarkError.text('Oops! You missed this. Please complete the field.');
            }

            let status = false;
            if (categoryValue && titleInput && durationInput && perQuestionMarkInput) {
                status = true;
            }
            return status;
        }

        function validateQuestionsWrapper() {
            let questionsWrapper = $('#questionsWrapper');

            let contents = questionsWrapper.children();

            let errorStatus = 0;


            for (let index = 0; index < contents.length; index++) {

                const element = contents[index];

                var elementId = contents[index].attributes[0].textContent;

                let uniqueID = elementId.match(/\d+/)[0];

                let questionText = $(`input[name="questions[${uniqueID}][question_text]"]`);

                questionText.removeClass('notValid')
                if (!questionText.val()) {
                    questionText.addClass('notValid').focus()
                    errorStatus++
                }

                let optionLength = 4;

                let hasCheck = false;

                for (let index = 1; index <= optionLength; index++) {

                    let questionOption = $(`input[name="questions[${uniqueID}][option_${index}][text]"]`);

                    questionOption.removeClass('notValid')

                    if (questionOption.length > 0 && !questionOption.val()) {
                        questionOption.addClass('notValid').focus()

                        errorStatus++
                    }

                    let questionCheck = $(`input[name="questions[${uniqueID}][option_${index}][is_correct]"]`)[0];

                    if (questionCheck?.checked) {
                        hasCheck = true
                    }
                }

                let questionRadioOne = $(`input[name="questions[${uniqueID}][correct_option]"]`)[0];
                let questionRadioTwo = $(`input[name="questions[${uniqueID}][correct_option]"]`)[1];

                if (questionRadioOne && questionRadioTwo) {

                    if (questionRadioOne?.checked || questionRadioTwo?.checked) {
                        hasCheck = true
                    }
                }

                $(`#${elementId}_options`).removeClass('notValid')
                if (!hasCheck) {
                    $(`#${elementId}_options`).addClass('notValid')
                    errorStatus++
                }

            }

            return errorStatus > 0 ? false : true;
        }
    </script>
@endpush

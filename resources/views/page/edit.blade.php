@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Page Edit'))

@push('styles')
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.2.0/ckeditor5.css" />
    <style>
        .ck-editor__editable {
            min-height: 140px;
            caret-color: black;
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
                        <li class="breadcrumb-item"><a href="{{ route('page.index') }}">{{ __('Page') }}</a></li>
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
                            <h5 class="card-title py-2">{{ __('Edit Page Content') }}</h5>
                            <form action="{{ route('page.update', $page->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="row">
                                    <div class="mb-3">
                                        <textarea name="content" class="texteditor form-control description-item">{{ $page->content }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn bgBlue btn-dipBlue">{{ __('Update') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/43.2.0/ckeditor5.umd.js"></script>

    <script>
        const {
            ClassicEditor,
            Essentials,
            Bold,
            Italic,
            Font,
            Paragraph,
            Alignment,
            BlockQuote,
            Heading,
            Indent,
            Link,
            List,
            Table,
            MediaEmbed,
            Image,
            ImageCaption,
            ImageResize,
            ImageToolbar,
            ImageStyle,
            ImageUpload,
            SourceEditing,
            CodeBlock,
            Underline,
            Strikethrough,
            Highlight,
            HorizontalLine
        } = CKEDITOR;

        ClassicEditor
            .create(document.querySelector('.texteditor'), {
                plugins: [
                    Essentials, Bold, Italic, Font, Paragraph, Alignment, BlockQuote, Heading, Indent, Link, List,
                    Table, MediaEmbed, Image, ImageCaption, ImageResize, ImageToolbar, ImageStyle, ImageUpload,
                    SourceEditing, CodeBlock, Underline, Strikethrough, Highlight, HorizontalLine
                ],
                toolbar: [
                    'undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'underline', 'strikethrough',
                    'highlight', '|',
                    'alignment', '|', 'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'link', 'blockquote', 'codeBlock', 'horizontalLine', '|', 'bulletedList', 'numberedList', '|',
                    'insertTable', 'mediaEmbed', 'imageUpload', '|', 'indent', 'outdent', '|', 'sourceEditing'
                ],
                image: {
                    toolbar: [
                        'imageTextAlternative', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side',
                        'linkImage'
                    ]
                },
                table: {
                    contentToolbar: [
                        'tableColumn', 'tableRow', 'mergeTableCells'
                    ]
                },
                mediaEmbed: {
                    previewsInData: true
                }
            })
            .then(editor => {
                console.log('Editor initialized successfully!');
            })
            .catch(error => {
                console.error(error);
            });
    </script>
@endpush

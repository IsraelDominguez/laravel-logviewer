@extends('genetsis-admin::layouts.admin-sections')

@section('section-card-header')
    @component('genetsis-admin::partials.card-header')
        @slot('card_title')
           {{ $viewer->getFilePath() }}
        @endslot

        <div class="btn-demo">
            @if (($viewer->getPrevPageUrl())||($viewer->getNextPageUrl()))
                @if ($viewer->getPrevPageUrl())
                    <a class="btn btn-secondary btn--icon waves-effect" href="{{ $viewer->getPrevPageUrl() }}"><i class="zmdi zmdi-arrow-back"></i></a>
                @endif
                @if ($viewer->getNextPageUrl())
                    <a class="btn btn-secondary btn--icon waves-effect" href="{{ $viewer->getNextPageUrl() }}"><i class="zmdi zmdi-arrow-forward"></i></a>
                @endif
            @endif
            <a class="btn btn-danger btn--icon waves-effect" href="{{route('log-viewer-delete', ['file'=> $viewer->file])}}"><i class="zmdi zmdi-delete"></i></a>
        </div>
    @endcomponent
@endsection

@section('section-content')
    <div class="row">
        <div class="col-12">
            <ul class="list-unstyled">
                <li> Size: {{ $size }} </li>
                <li> Updated at: {{ date('Y-m-d H:i:s', filectime($viewer->getFilePath())) }} </li>
            </ul>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
            <tr>
                <th>Level</th>
                <th>Env</th>
                <th>Time</th>
                <th>Message</th>
                <th></th>
            </tr>
            </thead>

            <tbody>

            @foreach($logs as $index => $log)

                <tr>
                    <td><span class="badge badge-{{ $viewer::$levelColors[$log['level']]}}">{{ $log['level'] }}</span></td>
                    <td><strong>{{ $log['env'] }}</strong></td>
                    <td style="width:160px;">{{ $log['time'] }}</td>
                    <td><code style="word-break: break-all;">{{ $log['info'] }}</code></td>
                    <td>
                        @if(!empty($log['trace']))
                            <a class="btn btn-secondary btn--icon waves-effect" data-toggle="collapse" data-target=".trace-{{$index}}"><i class="zmdi zmdi-more-vert"></i></a>
                        @endif
                    </td>
                </tr>

                @if (!empty($log['trace']))
                    <tr class="collapse trace-{{$index}}">
                        <td colspan="5"><div style="white-space: pre-wrap;background: #333;color: #fff; padding: 10px;">{{ $log['trace'] }}</div></td>
                    </tr>
                @endif

            @endforeach

            </tbody>
        </table>
    </div>
@endsection

@push('custom-js')
    @if ($message = Session::get('success'))
    <script>
        $(document).ready(function() {
            notify('{{ $message }}');
        });
    </script>
    @endif
@endpush

<li class="navigation__sub {{ ($section=='logs') ? 'navigation__sub--active' : '' }}">
    <a href=""><i class="zmdi zmdi-format-list-bulleted zmdi-hc-fw"></i> Logs</a>
    <ul>
        @foreach(\Logviewer::getLogFiles() as $logFile)
            <li class="{{ ($section=='campaign') ? 'navigation__active' : '' }}">
                <a href="{{ route('log-viewer-file', ['file' => $logFile]) }}">{{ $logFile }}</a>
            </li>
        @endforeach
    </ul>
</li>
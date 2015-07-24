@extends($theme_layout)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            @if(count($files) == 0)
                <p class="alert alert-info">Non hai files assegnati</p>
            @else
                <table class="table">
                    <tbody>
                        @foreach($files as $file)
                            <tr>
                                <td><a href="{{ url('file/' . $file) }}">{{ basename($file) }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="col-md-6">
            @if(count($groupfiles) == 0)
                <p class="alert alert-info">Il tuo gruppo non ha files assegnati</p>
            @else
                <table class="table">
                    <tbody>
                        @foreach($groupfiles as $file)
                            <tr>
                                <td><a href="{{ url('file/' . $file) }}">{{ basename($file) }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
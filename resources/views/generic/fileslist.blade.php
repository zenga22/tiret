<table class="table filelist filteratable">
    <tbody>
        @foreach($files as $file)
            <?php

            $new_document = false;
            $filename = basename($file);

            if ($user != null) {
                $document = App\Document::where('folder', $user->username)->where('filename', $filename)->first();
                if ($document == null || $document->downloaded == false)
                    $new_document = true;
            }

            ?>

            <tr>
                <td>
                    @if($new_document == true)
                        <strong>
                    @endif

                    <a href="{{ url('file/' . $file) }}">{{ $filename }} <span class="btn btn-default pull-right">Scarica File</span></a>

                    @if($new_document == true)
                        </strong>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

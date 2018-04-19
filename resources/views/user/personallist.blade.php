<ul class="nav nav-tabs" role="tablist">
    <?php $index = 0; $rands = [] ?>

    @foreach($file_groups as $name => $files)
        <li role="presentation" {!! $index++ == 0 ? 'class="active"' : '' !!}>
            <?php $r = rand(); $rands[$index] = $r ?>
            <a href="#{{ $name . $r }}" aria-controls="{{ $name }}" role="tab" data-toggle="tab">{{ $name }}</a>
        </li>
    @endforeach
</ul>

<div class="tab-content">
    <?php $index = 0 ?>

    @foreach($file_groups as $name => $files)
        <div role="tabpanel" class="tab-pane {{ $index++ == 0 ? 'active' : '' }}" id="{{ $name . $rands[$index] }}">
            <br>
            @if($files->type == 'groups')
                @include('user.personallist', ['file_groups' => $files->contents])
            @else
                @include('generic.fileslist', ['files' => $files->contents, 'user' => $user])
            @endif
        </div>
    @endforeach
</div>

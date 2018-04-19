@extends($theme_layout)

@section('content')
<ol class="breadcrumb">
    <li>Documenti</li>
    <li class="pull-right"><a href="{{ url('/auth/logout') }}">Logout</a></li>
    <li class="pull-right"><a href="{{ url('/user') }}">Cambia Password</a></li>
</ol>

<div class="container-fluid">
    @if(!empty($user->group->message))
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                {{ $user->group->message }}
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-md-9 my-files">
            <h4>I miei documenti</h4>

            @if(count($files) == 0)
                <p class="alert alert-info">Non hai files assegnati</p>
            @else
                @if(config('tiret.grouping_rules') != '')
                    <?php

                    if (function_exists('read_grouping_rules') == false) {
                        function read_grouping_rules($rules, $files) {
                            foreach($files as $file) {
                                $name = 'Altri';

                                preg_match($rules['regexp'], basename($file), $matches);
                                if (isset($matches['key']))
                                    if(isset($rules['enforced']) == false || in_array($matches['key'], $rules['enforced']))
                                        $name = $matches['key'];

                                if (isset($file_groups[$name]) == false)
                                    $file_groups[$name] = (object)[
                                        'type' => 'files',
                                        'contents' => []
                                    ];

                                $file_groups[$name]->contents[] = $file;
                            }

                            if (isset($rules['children'])) {
                                foreach($file_groups as $name => $sub_files) {
                                    $file_groups[$name] = (object)[
                                        'type' => 'groups',
                                        'contents' => read_grouping_rules($rules['children'], $sub_files->contents)
                                    ];
                                }
                            }

                            if (isset($rules['sorting'])) {
                                $sorter = $rules['sorting'];
                                if (isset($rules['sort_direction']) && $rules['sort_direction'] == 'reverse')
                                    $sorter = array_reverse($sorter);

                                $file_groups = array_replace(array_flip($sorter), $file_groups);
                                foreach($file_groups as $name => $value)
                                    if (!is_object($value))
                                        unset($file_groups[$name]);
                            }
                            else {
                                if (isset($rules['sort_direction']) && $rules['sort_direction'] == 'reverse')
                                    krsort($file_groups);
                                else
                                    ksort($file_groups);
                            }

                            return $file_groups;
                        }
                    }

                    $rules = config('tiret.grouping_rules');
                    $file_groups = read_grouping_rules($rules, $files);

                    ?>

                    @include('user.personallist', ['file_groups' => $file_groups])
                @else
                    <div class="tab-content">
                        @include('generic.fileslist', ['files' => $files, 'user' => $user])
                    </div>
                @endif
            @endif
        </div>
        <div class="col-md-3 group-files">
            @if(count($groupfiles) == 0)
                <p class="alert alert-info">Il tuo gruppo non ha files assegnati</p>
            @else
                @include('generic.fileslist', ['files' => $groupfiles, 'user' => null])
            @endif
        </div>
    </div>
</div>
@endsection

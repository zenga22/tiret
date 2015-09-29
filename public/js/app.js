$(document).ready(function() {
    $('.load-group-file').click(function() {
        var id = $(this).data('group');
        $('#loadFile input[name=group_id]').val(id);
    });

    if ($('#triggerform').length > 0)
        $('#triggerform').submit();

    $('#textfilter').keyup(function() {
        var t = $(this).val().toLowerCase();

        if (t == '') {
            $('.filelist td').show();
        }
        else {
            $('.filelist td').each(function() {
                var a = $(this).find('a').text().toLowerCase();
                if (a.indexOf(t) == -1)
                    $(this).hide();
                else
                    $(this).show();
            });
        }
    });
});

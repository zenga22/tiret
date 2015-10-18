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
            $('.filteratable tbody tr').show();
        }
        else {
            $('.filteratable tbody tr').each(function() {
                var a = $(this).find('td').text().toLowerCase();
                if (a.indexOf(t) == -1)
                    $(this).hide();
                else
                    $(this).show();
            });
        }
    });

    $('.waiting-count').each(function() {
        var folder = $(this).attr('id');
        var img = $(this);
        $.get('/admin/count', {folder: folder}, function(c) {
            img.parent().append('<span>' + c + '</span>');
            img.remove();
        });
    });
});

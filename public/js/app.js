$(document).ready(function() {
    $('.load-group-file').click(function() {
        var id = $(this).data('group');
        $('#loadFile input[name=group_id]').val(id);
    });

    if ($('#triggerform').length > 0)
        $('#triggerform').submit();
});

// Копирование
$('.copy').on('click', function () {

    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(this).find('#copy').text()).select();
    document.execCommand("copy");
    $temp.remove();

    note({
        content: "Cкопирован!",
        type: "success"
    });
})
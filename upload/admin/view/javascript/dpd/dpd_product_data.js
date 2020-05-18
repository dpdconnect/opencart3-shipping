$(document).ready(
    function () {
        $('#button-dpd-edit-product-data').on(
            'click', '', function (e) {
                e.preventDefault();
                dpdStartEdit(e.target);
                return false;
            }
        );
    }
);

function dpdStartEdit()
{
    var started = false;
    $('#dpd-container-product-data').each(
        function () {
            started = true;
        }
    );
    if (started) {
        return;
    }
    $('#button-dpd-edit-product-data').parent().append('<div id="dpd-container-product-data" class="panel panel-default" style="z-index: 22; position: absolute; width: 80%; top: 60px; left: 10%; background: white">Loading ...</div>')

    $('#dpd-container-product-data').load(
        $('#button-dpd-edit-product-data').data('href'),
        function (responseTxt, statusTxt, xhr) {
            if(statusTxt == "success") {
                dpdEditContainerLoaded();
            }
            if(statusTxt == "error") {
                console.debug("Error Loading DPD product data: " + xhr.status + ": " + xhr.statusText);
            }
        }
    );

}

function dpdEditContainerLoaded()
{
    $('#dpd-edit-product-data-form').submit(dpdEditFormSubmit);
    $('#dpd-cancel-edit-product-data').on(
        'click', '', function (e) {
            $('#dpd-container-product-data').remove();
        }
    );
    $('#dpd-submit-product-data').on(
        'click', '', function (e) {
            $('#dpd-edit-product-data-form').submit();
        }
    );
}

function dpdEditFormSubmit(e)
{
    e.preventDefault();
    var form = $(this);
    var url = $('#button-dpd-edit-product-data').data('href')

    $.ajax(
        {
            type: "POST",
            url: url,
            data: form.serialize(),
            error: function (result) {
                console.debug(JSON.stringify(result));
            },
            success: function (data) {
                $('#dpd-container-product-data').html(data);
                dpdEditContainerLoaded();
                var alerts = false;
                $('#dpd-container-product-data').find('div.alert').each(
                    function () {
                        alerts = true;
                    }
                )
                if (!alerts) {
                    $('#dpd-container-product-data').remove();
                }
            }
        }
    );
    return false;
}

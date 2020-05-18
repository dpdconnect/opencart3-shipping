$(document).ready(
    function () {

        $('.toggleError').on('click', '', dpdToggleError);
    }
);

function dpdToggleError(e)
{
    e.preventDefault();
    var errorRow = $('#shipmentError'+$(this).data('shipment_id'));
    if (errorRow.css('display') == 'none') {
        errorRow.show();
    } else {
        errorRow.hide();
    }
    return false;
}



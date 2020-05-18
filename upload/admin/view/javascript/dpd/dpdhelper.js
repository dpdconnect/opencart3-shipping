$(document).ready(
    function () {

        // Bug in prestashop, checking all doesn't trigger a change
        $('#form-order thead input[type=\'checkbox\']').on(
            'change', function () {
                handleChange();
            }
        );

        // IE and Edge fix!
        $('#button-dpd-scp-labels, #button-dpd-ncp-labels, #button-dpd-labels, #button-dpd-return-labels, #button-dpd-generate-shipping-list').on(
            'click', function (e) {
                $('#form-order').attr('action', this.getAttribute('formAction'));
            }
        );

        $('input[name^=\'selected\']').on(
            'change', function () {
                handleChange();
            }
        );

        dpdDisableButtons();

        function dpdDisableButtons()
        {
            $('#button-dpd-scp-labels').prop('disabled', true);
            $('#button-dpd-ncp-labels').prop('disabled', true);
            $('#button-dpd-labels-generate').prop('disabled', true);
            $('#button-dpd-return-labels-generate').prop('disabled', true);
            $('#button-dpd-labels-download').prop('disabled', true);
            $('#button-dpd-return-labels-download').prop('disabled', true);
            $('#button-dpd-generate-shipping-list').prop('disabled', true);
        }

        function handleChange()
        {
            dpdDisableButtons();

            var selected = $('input[name^=\'selected\']:checked');

            for (i = 0; i < selected.length; i++) {
                if ($(selected[i]).parent().find('input[name^=\'shipping_code\']').val().startsWith('dpdbenelux')) {
                    $('#button-dpd-labels').prop('disabled', false);
                    $('#button-dpd-scp-labels').prop('disabled', false);
                    $('#button-dpd-labels-generate').prop('disabled', false);
                    $('#button-dpd-return-labels-generate').prop('disabled', false);
                    $('#button-dpd-labels-download').prop('disabled', false);
                    $('#button-dpd-return-labels-download').prop('disabled', false);
                    $('#button-dpd-generate-shipping-list').prop('disabled', false);
                    break;
                }
            }
        }
    }
);
